const ctx = $("#cpuChart");
const cpu = $("#cpu");
const ram = $("#ram");

const addData = (chart, data) => {
    chart.data.datasets.forEach((dataset) => {
        dataset.data.push(data);
    });
    chart.update();
};

const removeFirstData = (chart) => {
    chart.data.datasets.forEach((dataset) => {
        dataset.data.shift();
    });
    chart.update();
};

let cpuChart = new Chart(ctx, {
    type: "line",
    data: {
        labels: [" ", " ", " ", " ", " ", " ", " ", " ", " ", " "],
        datasets: [
            {
                data: [],
                backgroundColor: "#008542",
                borderWidth: 1,
                lineTension: 0,
            },
        ],
    },
    options: {
        animation: {
            duration: 0,
        },
        elements: {
            point: {
                radius: 0,
            },
        },
        legend: {
            display: false,
        },
        scales: {
            yAxes: [
                {
                    ticks: {
                        beginAtZero: true,
                    },
                },
            ],
            xAxes: [
                {
                    gridLines: {
                        display: false,
                    },
                },
            ],
        },
        tooltips: {
            enabled: false,
            callbacks: {
                label: (tooltipItem) => {
                    return tooltipItem.yLabel;
                },
            },
        },
        responsive: true,
    },
});
Chart.defaults.global.defaultFontColor = "#ffffff";
Chart.defaults.global.animation = "linear";

// Recieve monitoring datas from NodeJS server.
socket.on("monitoring", (data) => {
    const [cpu_usage, ram_usage] = data.split(" ");
    let datasLength = 0;

    cpu.html(cpu_usage);
    ram.html(ram_usage);
    cpuChart.data.datasets.forEach((dataset) => {
        datasLength = dataset.data.length;
    });
    if (datasLength === 10) {
        removeFirstData(cpuChart);
    }
    addData(cpuChart, cpu_usage);
});
