<div class="mt-2 mb-3 chart-refresh d-none">
    <button class="btn btn-info" onclick="location.reload();">
        <i class="fa fa-refresh"></i>
        Reload page to update the chart
    </button>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
<canvas style="max-width: 1100px" id="myChart"></canvas>

{{-- let xValues = {!! $data->performance['categories']->map(fn($d) => $data->performance['categories']->first(fn($d2) => $d->id == $d2->id)->name)->toJson() !!}; --}}
<script>
let xValues = {!! $data->performance['categories']->map(fn($d) => $d->name)->toJson() !!};
let yValues = {!! (collect($data->performance['categorized'])->map(fn($d) => $d['percentage'])->toJson()) !!};
yValues = Object.values(yValues)
new Chart("myChart", {
    type: "bar",
    data: {
        labels: xValues,
        datasets: [{
            label: "Performance Chart",
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 205, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(201, 203, 207, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 205, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(201, 203, 207, 0.2)',
            ],
            borderColor: [
                'rgb(255, 99, 132)',
                'rgb(255, 159, 64)',
                'rgb(255, 205, 86)',
                'rgb(75, 192, 192)',
                'rgb(54, 162, 235)',
                'rgb(153, 102, 255)',
                'rgb(201, 203, 207)',
                'rgb(255, 159, 64)',
                'rgb(255, 205, 86)',
                'rgb(75, 192, 192)',
                'rgb(54, 162, 235)',
                'rgb(153, 102, 255)',
                'rgb(201, 203, 207)',
            ],
            borderWidth: 1,
            data: yValues
        }]
    },
});
</script>
