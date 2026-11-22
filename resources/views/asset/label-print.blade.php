@extends('layouts.master')
@section('title', 'Label Print')
@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<style>
    .barc {
        margin-top: -10px
    }
    .barc svg{
        width: auto;
        max-width: 80%;
        margin: auto;
    }
    @page {
        size: 80mm 297mm !important; /* Set the width and height */
        margin: 0 !important; /* Remove margins if needed */
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Label Print</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('assets-management.index') }}">Assets</a></li>
                        <li class="breadcrumb-item active">Label Print</li>
                    </ul>
                </div>
            </div>
        </div>

        <div style="margin: auto; max-width: 500px;">
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label class="col-form-label"> Header:</label>
                        <input type="text" name="header" class="form-control" required value="{{ $company_info['name'] ?? '' }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9">
                    <button class="btn-block btn btn-primary" onclick="generateLabel(this)">
                        Generate
                    </button>
                </div>
                <div class="col-md-3">
                    <button class="btn-block btn btn-primary" onclick="printMe('print_area')">
                        Print
                    </button>
                </div>
            </div>
            <div class="mt-5">
                <div style="padding: 10px;margin: auto; max-width: 400px;"
                    id="print_area_container"
                >
                    <div id="print_area"
                        style="width: 400px !important; margin: auto; text-align: left;"
                    >

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    let assets = @json($assets);
    async function generateLabel() {
        let header = document.querySelector('input[name="header"]').value;

        $('#print_area').html(``)
        for (let i = 0; i < assets.length; i++) {
            let asset = assets[i];
            $('#print_area').append(`
                <div style="
                    width: 187.82px;
                    height: 110px;
                    margin-right: 4.2px;
                    margin-left: 4.2px;
                    margin-bottom: 8.64px;
                    text-align: center;
                    line-height: normal;
                    background-color: #fff;
                    display: inline-block;
                    text-align: left;
                    overflow: hidden;
                ">
                    <div style="
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    height: 100%;
                    ">
                        <b>${header}</b>
                        <span style="font-size: 11px; padding-bottom: 4px;">${asset.name}</span>
                        <div class="barc barcode${i}" style="height: 40px; text-align: center">
                            <svg></svg>
                        </div>
                        <span style="font-size: 11px;">${asset.tag}</span>
                    </div>
                </div>
            `);
            let select = ".barcode" + i + " svg"
            JsBarcode(select, asset.tag, {
                format: "CODE128",
                lineColor: "#333",
                displayValue: false,
                height: 30,
            });
        }
        $('.barc svg').attr('height', 40)
        $('.barc svg').attr('width', "100%")
        $('.barc svg' + " rect").attr('height', 40)
    }
    generateLabel()
    function printMe(DivID) {
        var disp_setting="toolbar=yes,location=no,";
        disp_setting+="directories=yes,menubar=yes,";
        disp_setting+="scrollbars=yes,width=410, height=600, left=100, top=25";
            var content_vlue = document.getElementById("print_area_container").innerHTML;
            var docprint=window.open("","",disp_setting);
            docprint.document.open();
            docprint.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"');
            docprint.document.write('"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
            docprint.document.write('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">');
            docprint.document.write('<head><title>Barcodes</title>');
            docprint.document.write(`
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&display=swap" rel="stylesheet">
            <style>
                .barcode {
                    font-family: "Libre Barcode 128", system-ui;
                    font-weight: 400;
                    font-style: normal;
                }
            </style>
            `);
            docprint.document.write('<style type="text/css">body{ margin:0px;');
            docprint.document.write('font-family:verdana,Arial;color:#000;');
            docprint.document.write('font-family:Verdana, Geneva, sans-serif; font-size:12px;}');
            docprint.document.write('a{color:#000;text-decoration:none;} </style>');
            docprint.document.write('</head><body onLoad="self.print()"><center>');
            docprint.document.write(content_vlue.replace('400px', '405px'));
            docprint.document.write('</center></body></html>');
            docprint.document.close();
            docprint.focus();
        }
</script>
@endsection
