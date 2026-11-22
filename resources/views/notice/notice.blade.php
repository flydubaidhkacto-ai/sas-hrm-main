@extends('layouts.master')
@section('title', "Notice Board")
@section('content')
<style>
    .designation {
        color: unset;
        font-size: unset;
    }
</style>
<!-- Page Wrapper -->
<div class="page-wrapper">

    <!-- Page Content -->
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Notice Board</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Notice Board</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="{{ route('notice.create') }}" class="btn add-btn"><i
                            class="fa fa-plus"></i> Add Notice</a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped custom-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>Title </th>
                                <th>Description </th>
                                <th>Views</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notices as $key => $notice)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td hidden class="id">{{ $notice->id }}</td>
                                <td class="title">{{ $notice->title }}</td>
                                <td class="description">
                                    {!! \Str::limit(str_replace("&nbsp; &nbsp;", '', strip_tags($notice->description)), 100) !!}
                                    @if ($notice->attachments)
                                        <br/>
                                        @foreach ($notice->attachments as $item)
                                            <div class="mb-1">
                                                <i class="fa fa-file{{ $item->is_pdf ? '-pdf-o' : '' }}"></i>
                                                {{ $item->type }}
                                                @if($item->is_pdf)
                                                    <button type="button" class="btn btn-sm btn-info pdf-preview-btn" data-url="{{ $item->url }}" data-name="{{ $item->type }}">
                                                        <i class="fa fa-eye"></i> Preview
                                                    </button>
                                                @endif
                                                <a class="link" href="/uploaded_files/{{ $item->path }}" download="{{ $item->file_name }}">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            </div>
                                        @endforeach
                                    @endif
                                </td>
                                 <td><span class="notice-viewers-btn" style="cursor: pointer; text-decoration: underline; color: #1071d6;" data-notice-id="{{ $notice->id }}" data-notice-title="{{ $notice->title }}">{{ $notice->user_view_count }}/{{ $total_users }}</span></td>
                                <td class="text-right">
                                    <a class="btn btn-sm btn-info" href="{{ route('notice.show', $notice) }}">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                    <a class="btn btn-sm btn-primary" href="{{ route('notice.edit', $notice) }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a class="delete_notice btn btn-sm btn-danger" href="#" data-toggle="modal"
                                        data-target="#delete_notice"><i class="fa fa-trash"></i>
                                        </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    {{ $notices->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Content -->

    <!-- Delete Notice Modal -->
    <div class="modal custom-modal fade" id="delete_notice" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>Delete Notice</h3>
                        <p>Are you sure want to delete?</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('notice.delete') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="id" class="d_id" value="">
                            <div class="row">
                                <div class="col-6">
                                    <button type="submit"
                                        class="btn btn-primary continue-btn submit-btn">Delete</button>
                                </div>
                                <div class="col-6">
                                    <a href="javascript:void(0);" data-dismiss="modal"
                                        class="btn btn-primary cancel-btn">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Delete Notice Modal -->

</div>
<!-- /Page Wrapper -->

<!-- /Page Wrapper -->
@include('notice.partials.pdf-preview-modal')
@include('notice.partials.notice-viewers-modal')
@section('script')
<script src="https://cdn.ckeditor.com/ckeditor5/34.0.0/classic/ckeditor.js"></script>
{{-- update js --}}
{{-- <script>
    ClassicEditor.create(document.querySelector('.add_editor'))
    ClassicEditor.create(document.querySelector('.editor_edit'))


</script>
<script>
    $(document).on('click','.edit_notice',function()
        {
            var _this = $(this).parents('tr');
            $('#edit_notice [name=d_id]').val(_this.find('.id').text().trim());
            $('#edit_notice .title').val(_this.find('.title').text());
            $('#edit_notice .description').val(_this.find('.description').text());
        });

</script> --}}

<script>
    ClassicEditor.create(document.querySelector('.add_editor'),{
        height: '500px'
    })

    let editorInstance;

    ClassicEditor.create(document.querySelector('.editor_edit'), {
        height: '500px'
    }).then(editor => {
        editorInstance = editor;
    }).catch(error => console.error('Error initializing CKEditor for editor_edit:', error));

    $(document).on('click', '.edit_notice', function () {
        var _this = $(this).parents('tr');

        $('#edit_notice [name=d_id]').val(_this.find('.id').text().trim());
        $('#edit_notice .title').val(_this.find('.title').text().trim());

        const description = _this.find('.description').text().trim();
        if (editorInstance) {
            editorInstance.setData(description);
        }

    });

</script>

{{-- delete model --}}
<script>
    $(document).on('click','.delete_notice',function()
        {
            var _this = $(this).parents('tr');
            console.log(_this.find('.id').text().trim());
            $('#delete_notice .d_id').val(_this.find('.id').text().trim());
        });
</script>
@endsection
@endsection
