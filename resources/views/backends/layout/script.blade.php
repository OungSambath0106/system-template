<!-- jQuery -->


<script src="{{ asset('backend/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('backend/sweetalert2/js/sweetalert2@10.js') }}"></script>
<script src="{{ asset('backend/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
<!-- Bootstrap 4 -->
<script src="{{ asset('backend/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<!-- AdminLTE App -->
<script src="{{ asset('backend/dist/js/adminlte.min.js') }}"></script>
<!-- DataTables  & Plugins -->
<script src="{{ asset('backend/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('backend/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('backend/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('backend/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('backend/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('backend/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('backend/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('backend/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('backend/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('backend/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('backend/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>

<script src="{{ asset('backend/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/plugins/inputmask/jquery.inputmask.min.js') }}"></script>
<script src="{{ asset('backend/plugins/daterangepicker/daterangepicker.js') }}"></script>
<!-- Datepicker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

{{-- <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" /> --}}
{{-- summernote --}}
<script src="{{ asset('backend/plugins/summernote/summernote-bs4.min.js') }}"></script>

<!-- AdminLTE for demo purposes -->
{{-- <script src="{{ asset('backend/dist/js/demo.js') }}"></script> --}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.js"></script>

{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
<script src="{{ asset('backend/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script src="{{ asset('backend/plugins/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('js/compress.js') }}"></script>
<script src="{{ asset('js/rowfy.js') }}"></script>

{{ Session::has('message') }}

<script>
    $(function() {

        $(".thumbnail").fancybox();

        $(document).on("click", ".btn-modal", function(e) {
            e.preventDefault();
            var container = $(this).data("container");

            $.ajax({
                url: $(this).data("href"),
                dataType: "html",
                success: function(result) {
                    $(container).html(result).modal("show");
                    $('.select2').select2();
                },
            });
        });
        //Initialize Select2 Elements
        $('.select2').select2({
            placeholder: `{{ __('Please Select') }}`,
            allowClear: true
        });

        // init custom file input
        bsCustomFileInput.init();

        // init summernote
        $('.summernote').summernote({
            placeholder: '{{ __('Type something') }}',
            tabsize: 2,
            height: $('.summernote').data('height') ?? 300
        });
        $('.in_kind_support_summernote').summernote({
            placeholder: '{{ __('Type something') }}',
            tabsize: 2,
            height: 100,
            width: 500
        });

        $('.datepicker').datepicker({
            language: "es",
            autoclose: true,
            format: "dd-mm-yyyy"
        });


        //   $(".table").DataTable({
        //     "responsive": true, "lengthChange": false, "autoWidth": false,
        //     "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        //   }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        //   $('#example2').DataTable({
        //     "paging": true,
        //     "lengthChange": false,
        //     "searching": false,
        //     "ordering": true,
        //     "info": true,
        //     "autoWidth": false,
        //     "responsive": true,
        //   });
    });
</script>

<script>
    $(document).ready(function() {
        // alert(1);
        var success_audio = "{{ URL::asset('sound/success.wav') }}";
        var error_audio = "{{ URL::asset('sound/error.wav') }}";
        var success = new Audio(success_audio);
        var error = new Audio(error_audio);

        const Confirmation = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger'
            },
            buttonsStyling: false
        });

        @if (Session::has('msg'))
            @if (Session::get('success') == true)
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true
                }
                toastr.success("{{ Session::get('msg') }}");
                success.play();
            @else
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true
                }
                toastr.error("{{ Session::get('msg') }}");
                error.play();
            @endif
        @endif

    });
</script>

@stack('js')
