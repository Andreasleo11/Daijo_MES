<x-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight">
            Daftar Operator
        </h2>
    </x-slot>

    <div class="py-2 print:py-0">
        <div class="max-w-full mx-auto sm:px-2 lg:px-4 print:px-0">
            <div class="bg-white shadow sm:rounded-lg p-2 print:p-0 overflow-x-auto">
                <table class="w-full text-xs text-gray-800 border border-collapse">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="border px-2 py-1 text-left">Nama Operator</th>
                            <th class="border px-2 py-1 text-left">Foto</th>
                            <th class="border px-2 py-1 text-left">Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($operators as $operator)
                            <tr class="break-inside-avoid">
                                <td class="border px-2 py-1">{{ $operator->name }}</td>
                                <td class="border px-2 py-1">
                                    {{ $operator->profile_picture ? 'ADA' : 'BELUM ADA' }}
                                </td>
                                <td class="border px-2 py-1">
                                    {{ $operator->position ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            body {
                font-size: 10px;
                line-height: 1.2;
            }

            th, td {
                padding: 2px 4px !important;
            }

            .print\:py-0 {
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }

            .print\:px-0 {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .break-inside-avoid {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .sm\:rounded-lg, .shadow {
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }
    </style>
</x-dashboard-layout>
