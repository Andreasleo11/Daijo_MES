<div class="dashboard-container">
    <header class="dashboard-header">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
        <!-- Add other dashboard-specific content like navigation here -->
    </header>
    
    <main class="dashboard-content">
        <!-- Slot for page-specific content -->
        {{ $slot }}
    </main>
    
    <footer class="dashboard-footer">
        <p class="text-sm text-gray-600">© {{ date('Y') }} Your Company</p>
    </footer>
</div>