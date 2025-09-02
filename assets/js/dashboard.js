// dashboard.js
const ctx = document.getElementById('lineChart').getContext('2d');

// The PHP variables are passed via a global JS object
const chartData = window.chartData;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartData.timestamps,
        datasets: [
            {
                label: 'Temperature',
                data: chartData.temp,
                borderColor: 'red',
                fill: false
            },
            {
                label: 'pH',
                data: chartData.ph,
                borderColor: 'blue',
                fill: false
            },
            {
                label: 'DO Level',
                data: chartData.do,
                borderColor: 'green',
                fill: false
            }
        ]
    }
});
