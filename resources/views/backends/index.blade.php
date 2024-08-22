@extends('backends.master')
@section('page_title')
    Admin Dashboard
@endsection
@push('css')
    <style>
        .amount {
            font-size: 40px !important;
            color: white;
            font-family: Arial, Helvetica, sans-serif;
        }

        h4 {
            font-family: Arial, Helvetica, sans-serif;
            color: white;
        }

        .summary-footer a {
            color: white;
        }

        .summary-footer a:hover {
            text-decoration: underline !important;
            color: white;
        }
    </style>
@endpush
@section('contents')
    <div class="section-body">
        <div class="col-md-12 ">
            <div class="row justify-content-center p-4 ">
                <div class="col-xs-6 col-md-3 col-sm-6 text-center">
                    <section class="card bg-warning">
                        <div class="card-body">
                            <div class="widget-summary">
                                <div class="widget-summary-col">
                                    <div class="summary">
                                        <h4 class="title ">Total Users</h4>
                                        <div class="info">
                                            <strong class="amount">{{ $totalusers }}</strong>
                                        </div>
                                    </div>
                                    <div class="summary-footer">
                                        <a href="{{ route('admin.user.index') }}" class="text text-uppercase">Users List</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="col-xs-6 col-md-3 col-sm-6 text-center">
                    <section class="card bg-info">
                        <div class="card-body">
                            <div class="widget-summary">
                                <div class="widget-summary-col">
                                    <div class="summary">
                                        <h4 class="title">Total Campuses</h4>
                                        <div class="info">
                                            <strong class="amount">{{ $total_compuses ?? 0 }}</strong>
                                        </div>
                                    </div>
                                    <div class="summary-footer">
                                        <a href="{{ route('admin.compus.index') }}" class="text text-uppercase">
                                            Campuses List
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="col-xs-6 col-md-3 col-sm-6 text-center">
                    <section class="card bg-success">
                        <div class="card-body">
                            <div class="widget-summary">
                                <div class="widget-summary-col">
                                    <div class="summary">
                                        <h4 class="title">Total Recruitments</h4>
                                        <div class="info">
                                            <strong class="amount">{{ $totalrecruitments }}</strong>
                                        </div>
                                    </div>
                                    <div class="summary-footer">
                                        <a href="{{ route('admin.recruitment.index') }}"
                                            class="text text-uppercase">Recruitments
                                            List</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="col-xs-6 col-md-3 col-sm-6 text-center">
                    <section class="card bg-danger">
                        <div class="card-body">
                            <div class="widget-summary">
                                <div class="widget-summary-col">
                                    <div class="summary">
                                        <h4 class="title">Total Blogs</h4>
                                        <div class="info">
                                            <strong class="amount">11</strong>
                                        </div>
                                    </div>
                                    <div class="summary-footer">
                                        <a href="#" class="text text-uppercase">Blog List</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <section class="card col-md-12 ">
                    <div class="card-body ">
                        <div class="chartjs-size-monitor"
                            style="position: absolute; inset: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;">
                            <div class="chartjs-size-monitor-expand"
                                style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;">
                                <div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div>
                            </div>
                            <div class="chartjs-size-monitor-shrink"
                                style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;">
                                <div style="position:absolute;width:200%;height:200%;left:0; top:0"></div>
                            </div>
                        </div>
                        <canvas id="myChart" width="1448" height="723"
                            style="display: block; height: 579px; width: 1159px;" class="chartjs-render-monitor"></canvas>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <!-- Development version -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js/dist/Chart.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('myChart').getContext('2d');
            var currentDate = new Date();
            var currentMonth = currentDate.getMonth() + 1; // Get the current month (1-indexed)
            var currentYear = currentDate.getFullYear();
            var labels = [];

            // Generate labels for the first 14 days of the current month
            for (var i = 1; i <= 14; i++) {
                labels.push(currentMonth + "/" + i);
            }

            var userData = {!! json_encode($users) !!};

            // Extract the count of users for each month
            var userDataCounts = userData.map(user => user.count);

            var myChart = new Chart(ctx, {
                type: 'line', // Specify the type of chart (e.g., 'line', 'bar', 'pie')
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Registered Users',
                        data: userDataCounts, // Use user data counts instead of sample data
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
@endpush
