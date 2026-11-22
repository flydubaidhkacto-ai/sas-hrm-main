<script>
    window.super_department = "{{ request()->super_department ?? '' }}"
    window.department = "{{ request()->department ?? '' }}"
    window.designation = "{{ request()->designation ?? '' }}"

    if(
        $('.super-department-select2').length
    )
    {
        $('.super-department-select2').select2({
            ajax: {
                url: '{{ route('super_departments.select2') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                    }
                },
                processResults: function (data) {
                    return {
                        results: data,
                    }
                },
                cache: true,
            },
            placeholder: 'Select Super Department',
            allowClear: true,
            minimumInputLength: 1,
        }).on("change", function (e) {
            window.super_department = $(this).val()
            $('.department-select2').val(null).trigger('change')
            $('.employee-select2').val(null).trigger('change')
            $('.student-select2').val(null).trigger('change')
        });
    }
    $('.department-select2').select2({
        ajax: {
            url: '{{ route('departments.select2') }}',
            dataType: 'json',
            data: function (params) {
                return {
                    q: params.term,
                    super_department: window.super_department || window.selected_super_department || null,
                }
            },
            processResults: function (data) {
                return {
                    results: data,
                }
            },
            cache: true,
        },
        placeholder: 'Select Department',
        allowClear: true,
    }).on("change", function (e) {
        window.department = $(this).val()
        $('.designation-select2').val(null).trigger('change')
        $('.employee-select2').val(null).trigger('change')
        $('.student-select2').val(null).trigger('change')
    });
    $('.designation-select2').select2({
        ajax: {
            url: '{{ route('designations.select2') }}',
            dataType: 'json',
            data: function (params) {
                return {
                    q: params.term,
                }
            },
            processResults: function (data) {
                return {
                    results: data,
                }
            },
            cache: true,
        },
        placeholder: 'Select Designations',
        allowClear: true,
    }).on("change", function (e) {
        window.designation = $(this).val()
        $('.employee-select2').val(null).trigger('change')
        $('.student-select2').val(null).trigger('change')
    });
    $('.employee-select2').select2({
        ajax: {
            url: '{{ route('employees.select2') }}',
            dataType: 'json',
            data: function (params) {
                return {
                    q: params.term,
                    department: window.department || null,
                    super_department: window.super_department || window.selected_super_department || null,
                    designation: window.designation || null,
                }
            },
            processResults: function (data) {
                return {
                    results: data,
                }
            },
            cache: true,
        },
        placeholder: 'Select Employee',
        allowClear: true,
    })
    $('.student-select2').select2({
        ajax: {
            url: '{{ route('students.select2') }}',
            dataType: 'json',
            data: function (params) {
                return {
                    q: params.term,
                    department: window.department || null,
                }
            },
            processResults: function (data) {
                return {
                    results: data,
                }
            },
            cache: true,
        },
        placeholder: 'Select Student',
        allowClear: true,
    })

    $(document).ready(function () {
        $('select.init-select2').select2();
    })
</script>
