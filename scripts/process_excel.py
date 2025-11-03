import sys
import pandas as pd
import re
from datetime import datetime
import os

# Get file paths from Laravel
input_file = sys.argv[1]  # Original uploaded Excel file
output_file = sys.argv[2]  # Processed Excel file

# Ensure output directory exists
output_dir = os.path.dirname(output_file)
os.makedirs(output_dir, exist_ok=True)

# Load Excel file
df = pd.read_excel(input_file, skiprows=5)

# Identify date columns (assuming they start from column index 11)
date_columns = df.columns[11:]

# Get current year
current_year = datetime.now().year

# Date format counters
count_case_1 = 0  # [MM/DD]
count_case_2 = 0  # MM/DD~MM/DD
count_case_3 = 0  # DD-MMM
count_case_4 = 0  # MMM'YY
case_3_data = []
case_4_data = []

def parse_date(date_str):
    global count_case_1, count_case_2, count_case_3, count_case_4
    date_str = str(date_str).strip()

    # Remove newlines and anything after them (like "\nCKD")
    if "\n" in date_str:
        date_str = date_str.split("\n")[0].strip()

    # Remove any excess spaces
    date_str = date_str.replace("  ", " ").strip()

    # Case 1: [MM/DD]
    if re.match(r"^\[\d{2}/\d{2}\]$", date_str):
        date_str = date_str.strip("[]")
        count_case_1 += 1
        return f"{current_year}-{date_str[:2]}-{date_str[3:]}"
    
    # Case 2: MM/DD~MM/DD
    elif re.match(r"^\d{2}/\d{2}~\d{2}/\d{2}$", date_str):
        first_part = date_str.split("~")[0]
        count_case_2 += 1
        return f"{current_year}-{first_part[:2]}-{first_part[3:]}"
    
    # Case 3: DD-MMM (like "11-Mar" or "4-Mar")
    elif re.match(r"^\d{1,2}-[A-Za-z]{3}$", date_str):
        count_case_3 += 1
        case_3_data.append(date_str)
        try:
            return datetime.strptime(f"{date_str}-{current_year}", "%d-%b-%Y").strftime("%Y-%m-%d")
        except Exception as e:
            return None

    # Case 4: MMM'YY (like "May'25")
    elif re.match(r"^[A-Za-z]{3}'\d{2}$", date_str):
        case_4_data.append(date_str)
        month = date_str[:3]
        year_suffix = int(date_str[4:])
        year_full = 2000 + year_suffix if year_suffix < 50 else 1900 + year_suffix
        count_case_4 += 1
        return f"{year_full}-{datetime.strptime(month, '%b').month:02d}-01"

    return None

# Normalize data
normalized_data = []
for _, row in df.iterrows():
    # Use row['Cust. PN'] as the item_code
    item_code = row['Cust. PN']
    for date_col in date_columns:
        delivery_quantity = pd.to_numeric(row[date_col], errors='coerce')
        if pd.notna(delivery_quantity) and delivery_quantity > 0:
            delivery_date = parse_date(date_col)
            # Only add row if item_code, delivery_date and delivery_quantity are not null/empty
            if item_code and pd.notna(item_code) and delivery_date:
                normalized_data.append([
                    row['No'], row['Supplier'], row['PART CLASS'], row['Project'], row['Type'], 
                    row['QAD'], item_code, row['SNP'], row['Part Name'], 
                    delivery_date, delivery_quantity
                ])

# Create a new DataFrame
normalized_df = pd.DataFrame(normalized_data, columns=[
    'No', 'Supplier', 'PART CLASS', 'Project', 'Type', 
    'QAD', 'item_code', 'SNP', 'Part Name', 'delivery_date', 'delivery_quantity'
])

# Optionally, you can also drop rows with nulls in key columns:
normalized_df.dropna(subset=['item_code', 'delivery_date', 'delivery_quantity'], inplace=True)

# Overwrite existing output file
normalized_df.to_excel(output_file, index=False)

# Print logs (for debugging)
print(f"Case 1 ([MM/DD]) count: {count_case_1}")
print(f"Case 2 (MM/DD~MM/DD) count: {count_case_2}")
print(f"Case 3 (DD-MMM) Count: {count_case_3}")
print(f"Case 3 (DD-MMM) Data: {case_3_data}")
print(f"Case 4 (MMM'YY) Count: {count_case_4}")
print(f"Case 4 (MMM'YY) Data: {case_4_data}")
print(f"Processed data saved to {output_file}")
