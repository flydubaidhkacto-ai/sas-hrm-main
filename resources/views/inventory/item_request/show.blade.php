<form
    action="{{ route('approvals.approve', ['module' => 'inventory_item_request', 'module_id' => $inventory_request->id]) }}"
    method="post"
    class="inventory-show{{ $inventory_request->id }}"
>
    <div class="mb-2 ml-4">
        <div class="row">
            <div class="col-3">
                <img src="/uploaded_files/{{ $inventory_request->employee?->picture_file }}" alt="" class="w-100">
            </div>
            <div class="col-9">
                <h4>{{ $inventory_request->employee?->name }}</h4>
                <p class="text-muted mb-0">{{ $inventory_request->employee?->email }}</p>
                <p class="text-muted mb-0">{{ $inventory_request->employee?->phone }}</p>
                <p class="text-muted mb-0">{{ $inventory_request->employee?->address }}</p>
            </div>
        </div>
    </div>
    <div class="mt-4 ml-4">
        <table class="table table-striped" >
            <h3 for="">Item Details:</h3>
            <thead>
                <tr>
                    <th scope="col">Item Name</th>
                    <th scope="col">Code</th>
                    <th scope="col">Quantity</th>
                    {{-- <th scope="col">Barcodes</th> --}}
                </tr>
            </thead>
            <tbody>
                @foreach($inventory_request->items as $item)
                <tr>
                    <td>{{ $item->item->name }}</td>
                    <td>{{ $item->item->code }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>
                        @if($item->quantity > 1)
                        <table>
                            <tbody>
                        @endif
                                {{-- @forelse (\App\Models\InventoryItemRequest::getItemBarcodes($item->id) as $codes)
                                    <tr>
                                        <td>{{ $codes->barcode }}</td>
                                    </tr>
                                @empty
                                    @for ($i = 0; $i < $item->quantity; $i++)
                                        <input type="text" class="form-control barcodes" id="barcode_{{ $item->id }}_{{ $i }}"
                                            name="barcodes[{{ $item->id }}][{{ $item->inventory_item_id }}][]"
                                            placeholder="Barcode {{ $item->quantity > 1 ? $i + 1 : '' }}"
                                            >
                                    @endfor
                                @endforelse --}}
                            @if($item->quantity > 1)
                            </tbody>
                        </table>
                            @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mb-2 ml-4">
        <h3 for="">Description:</h3>
        <div class="ml-4">
            <p style="font-size: 16px;">
                {{ $inventory_request->description }}
            </p>
        </div>
    </div>

    <div class="mt-2" >

        @include('layouts.approval', [
            'model' => $inventory_request,
            'model_type' => 'inventory_item_request',
            'employee' => $inventory_request->employee,
            'have_form' => false,
        ])

    </div>
</form>
<script>
    (function() {
        let cont = $('.inventory-show{{ $inventory_request->id }}')
        console.log(cont.find('.barcodes'));

        // cont.find('input.barcodes').on('keyup change', function() {
        //     let requestItemId = $(this).data('request-item-id'); // reuquest item id
        //     let itemId = $(this).data('item-id'); // real inventory item
        //     let barcode = $(this).val();
        //     console.log(requestItemId, itemId, barcode);
        //     let meta = cont.find('.meta-data')
        //     meta.find(`input[data-request-item-id="${requestItemId}"][data-item-id="${itemId}"]`).remove()
        //     meta.append(`
        //         <input type="hidden" data-request-item-id="${requestItemId}" data-item-id="${itemId}" name="barcodes[${requestItemId}][${itemId}][]" value="${barcode}">
        //     `)
        // })
    })()
</script>
