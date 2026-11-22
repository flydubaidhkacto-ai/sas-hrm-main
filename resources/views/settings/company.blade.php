
@extends('layouts.master')
@section('title', "Settings: Company")
@section('content')


{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.min.css" /> --}}

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.css"/>

{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.js"></script> --}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>

{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/js/bootstrap.min.js"></script> --}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js"></script>

<style type="text/css">
    img {

        display: block;

        max-width: 100%;

    }

    .preview {

        text-align: center;

        overflow: hidden;

        width: 160px;

        height: 160px;

        margin: 10px;

        border: 1px solid red;

    }

    /* input{

        margin-top:40px;

    } */

    .section{

        margin-top:150px;

        background:#fff;

        padding:50px 30px;

    }

    .modal-lg{

        max-width: 1000px !important;

    }

</style>


    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            {!! Toastr::message() !!}
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="row">
                            <div class="col-sm-12">
                                <h3 class="page-title">Company Info</h3>
                            </div>
                        </div>
                    </div>
                    <!-- /Page Header -->
                    @include('layouts.alerts')
                    <form method="POST" action="{{ route('company.info.save') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Company Name <span class="text-danger">*</span></label>
                                    <input name="name" class="form-control" type="text" value="{{ $info['name'] ?? old('name') ?? 'Hr payroll' }}">
                                </div>
                            </div>
                            {{-- <div class="col-sm-6">
                                <label for="logo">Upload Company Logo:</label>
                                <img src="{{ ($info['logo'] ?? false) ? '/uploaded_files/' . $info['logo'] : '' }}" class="w-100">
                                <input type="file" name="logo" id="logo" accept="image/*" class="form-control-file"
                                    onchange="$(this).parent().find('img')[0].src = window.URL.createObjectURL(this.files[0])"
                                >
                            </div> --}}

                            <div class="col-sm-6">
                                <div class="row">
                                    <div class="col-md-6" style="overflow: hidden">
                                        <label for="logo">Upload Company Logo:</label>
                                        <img src="{{ ($info['logo'] ?? false) ? '/uploaded_files/' . $info['logo'] : '' }}" class="w-50">
                                        <input type="file" name="image" accept="image/*" class="image">

                                        <input type="hidden" name="image_base64">

                                        <img src="" style="width: 200px;display: none;" class="show-image">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="logo">Favicon:</label>
                                        <img src="{{ ($info['favicon'] ?? false) ? '/uploaded_files/' . $info['favicon'] : '' }}" class="w-50">
                                        <input type="file" name="favicon" accept="image/*" class="form-control-file"
                                            onchange="$(this).parent().find('img')[0].src = window.URL.createObjectURL(this.files[0])">
                                        <br>
                                        {{-- <small class="text-muted">Resolution: 50x50px <br> Size: 50kb</small> --}}
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label>Address</label>
                                    <input class="form-control" name="address" value="{{ $info['address'] ?? old('address') ?? '' }}" type="text">
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-6 col-lg-3">
                                <div class="form-group">
                                    <label>Country</label>
                                    <select class="form-control select" name="country">
                                        @foreach (config('services.countries') as $item)
                                            <option
                                                @if(($info['country'] ?? old('country') ?? null) === $item) selected @endif
                                                value="{{ $item }}">{{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-6 col-lg-3">
                                <div class="form-group">
                                    <label>City</label>
                                    <input class="form-control" name="city" value="{{ $info['city'] ?? old('city') ?? '' }}" type="text">
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-6 col-lg-3">
                                <div class="form-group">
                                    <label>Contact Person</label>
                                    <input class="form-control" name="contact_person" value="{{ $info['contact_person'] ?? old('contact_person') ?? '' }}" type="text">
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-6 col-lg-3">
                                <div class="form-group">
                                    <label>Postal Code</label>
                                    <input class="form-control" value="{{ $info['postal_code'] ?? old('postal_code') ?? '' }}" name="postal_code" type="text">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input class="form-control" value="{{ $info['email'] ?? old('email') ?? '' }}" name="email" type="email">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input class="form-control" value="{{ $info['phone_number'] ?? old('phone_number') ?? '' }}" name="phone_number" type="text">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label>Website Url</label>
                                    <input class="form-control" value="{{ $info['website_url'] ?? old('website_url') ?? '' }}" name="website_url" type="text">
                                </div>
                            </div>
                        </div>
                        <div class="submit-section">
                            <button type="submit" class="btn btn-primary submit-btn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cropModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">

        <div class="modal-dialog modal-lg" role="document">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title" id="modalLabel">Laravel Crop Image Before Upload Example</h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('.modal').modal('hide')">

                    <span aria-hidden="true">×</span>

                    </button>

                </div>

                <div class="modal-body">

                    <div class="img-container">

                        <div class="row">

                            <div class="col-md-8">

                                <img id="image" src="https://avatars0.githubusercontent.com/u/3456749">

                            </div>

                            <div class="col-md-4">

                                <div class="preview"></div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('.modal').modal('hide')">Cancel</button>

                    <button type="button" class="btn btn-primary" id="crop">Crop</button>

                </div>

            </div>

        </div>

    </div>
@endsection
@section('script')
<script>
    var $modal = $('#modal');
    var image = document.getElementById('image')
    $("body").on("change", ".image", function(e){

        var files = e.target.files;
        var reader = new FileReader();
        reader.readAsDataURL(files[0]);
        reader.onloadend = function() {
            var base64data = reader.result;
            $("input[name='image_base64']").val(base64data);
            $(".show-image").show();
            $(".show-image").attr("src",base64data);
        }
    });
</script>
@endsection
