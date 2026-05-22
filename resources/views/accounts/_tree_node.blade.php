{{-- resources/views/accounts/_tree_node.blade.php --}}
@php
    $colors = ['asset'=>'blue','liability'=>'red','equity'=>'purple','revenue'=>'green','expense'=>'orange'];
    $c = $colors[$account->type] ?? 'gray';
    $indent = $depth * 24;
@endphp
<div class="flex items-center gap-2 py-1.5 hover:bg-gray-50 rounded-lg px-2 transition"
     style="padding-right: {{ 8 + $indent }}px">
    @if($account->children->count())
        <i class="fa fa-folder text-{{ $c }}-400 w-4 text-center flex-shrink-0"></i>
    @else
        <i class="fa fa-file-invoice text-{{ $c }}-300 w-4 text-center flex-shrink-0"></i>
    @endif
    <span class="font-mono text-xs text-gray-400 w-12 flex-shrink-0">{{ $account->code }}</span>
    <span class="font-medium text-gray-700">{{ $account->name_ar }}</span>
    @if(!$account->is_leaf)
        <span class="text-xs text-gray-400">({{ $account->children->count() }} فرعي)</span>
    @endif
    @if($account->is_leaf)
        <a href="{{ route('accounts.ledger', $account) }}"
           class="mr-auto text-xs text-blue-500 hover:underline">دفتر الأستاذ</a>
    @endif
</div>
@foreach($account->children as $child)
    @include('accounts._tree_node', ['account' => $child, 'depth' => $depth + 1])
@endforeach
