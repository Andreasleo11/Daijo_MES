<x-dashboard-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-gray-100 py-12">
        {{-- Title --}}
        <div class="text-center mb-16">
            <h1 class="text-6xl font-bold text-slate-800 tracking-tight mb-2">DAIJO MES</h1>
            <div class="w-24 h-1 bg-gradient-to-r from-slate-600 to-slate-400 mx-auto rounded-full"></div>
        </div>

        {{-- Grid Menu --}}
        <div class="container mx-auto px-6 max-w-6xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($categories as $category)
                    @if($category['active'])
                        <a href="{{ $category['route'] }}" 
                           class="group relative bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-200 hover:border-slate-300 hover:-translate-y-1">
                            {{-- Subtle accent line --}}
                            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-slate-600 to-slate-400 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            
                            <div class="flex flex-col items-center text-center space-y-4">
                                <div class="p-4 bg-slate-50 rounded-xl group-hover:bg-slate-100 transition-colors duration-300">
                                    <i class="{{ $category['icon'] }} text-slate-600 text-3xl group-hover:scale-110 transition-transform duration-300"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-slate-800 mb-2">{{ $category['name'] }}</h3>
                                    @if(!empty($category['desc']))
                                        <p class="text-sm text-slate-500 leading-relaxed">{{ $category['desc'] }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            {{-- Hover arrow --}}
                            <div class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <i class="fas fa-arrow-right text-slate-400 text-sm"></i>
                            </div>
                        </a>
                    @else
                        <div class="relative bg-slate-50 p-8 rounded-2xl shadow-sm border border-slate-200 opacity-70">
                            <div class="flex flex-col items-center text-center space-y-4">
                                <div class="p-4 bg-slate-100 rounded-xl">
                                    <i class="{{ $category['icon'] }} text-slate-400 text-3xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-slate-600 mb-2">{{ $category['name'] }}</h3>
                                    @if(!empty($category['desc']))
                                        <p class="text-sm text-slate-400 leading-relaxed mb-3">{{ $category['desc'] }}</p>
                                    @endif
                                    <span class="inline-block px-3 py-1 text-xs font-medium text-amber-600 bg-amber-50 rounded-full border border-amber-200">
                                        Coming Soon
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Optional footer info --}}
        <div class="text-center mt-16">
            <p class="text-slate-400 text-sm">Manufacturing Excellence System</p>
        </div>
    </div>
</x-dashboard-layout>