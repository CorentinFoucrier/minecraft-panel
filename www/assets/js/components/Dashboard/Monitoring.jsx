import React from "react";
import { Line } from "react-chartjs-2";

const Monitoring = ({ data }) => {
    return (
        <Line
            redraw
            data={data}
            style={{
                background: "#3B3B3B",
                borderRadius: "0px",
                padding: "0"
            }}
            width={500}
            height={200}
            options={{
                animation: {
                    duration: 0
                },
                elements: {
                    point: {
                        radius: 0
                    }
                },
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [
                        {
                            ticks: {
                                beginAtZero: true,
                                suggestedMax: 100
                            }
                        }
                    ],
                    xAxes: [
                        {
                            gridLines: {
                                display: false
                            }
                        }
                    ]
                },
                tooltips: {
                    enabled: false,
                    callbacks: {
                        label: tooltipItem => {
                            return tooltipItem.yLabel;
                        }
                    }
                },
                responsive: true
            }}
        />
    );
};

export default Monitoring;
