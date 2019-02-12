
define(assets_path+"/assets/dist/charts/init", ["$", "highcharts"],
function(a) {
    "use strict";
    var b = a("$");
    return a("highcharts"),
    {
        line: function(a, c, d) {
            var e = {
                chart: {
                    defaultSeriesType: "spline"
                },
                title: "",
                xAxis: {
                    title: "",
                    lineWidth: 1,
                    lineColor: "#EEE",
                    type: "datetime",
                    endOnTick: !1,
                    startOnTick: !1,
                    dateTimeLabelFormats: {
                        second: "%H:%M:%S",
                        minute: "%H:%M",
                        hour: "%H:%M",
                        day: "%m-%e",
                        week: "%m-%e",
                        month: "%b'%y",
                        year: "%Y"
                    }
                },
                yAxis: {
                    title: "",
                    gridLineColor: "#EEE",
                    gridLineWidth: 1,
                    min: 0,
                    endOnTick: !1
                },
                tooltip: {
                    enabled: !0,
                    shadow: !1,
                    borderWidth: 0,
                    backgroundColor: "rgba(0,0, 0, .85)",
                    style: {
                        color: "#FFF",
                        fontSize: "12px"
                    },
                    formatter: function() {
                        return Highcharts.dateFormat("%Y年%m月%d日", this.x) + "<br /><strong>" + this.series.name + ":" + this.y + "</strong>"
                    }
                },
                series: "",
                plotOptions: {
                    spline: {
                        lineWidth: 2,
                        states: {
                            hover: {
                                lineWidth: 3
                            }
                        },
                        marker: {
                            enabled: !1,
                            states: {
                                hover: {
                                    enabled: !0,
                                    symbol: "circle",
                                    radius: 5,
                                    lineWidth: 1
                                }
                            }
                        },
                        shadow: !1
                    }
                },
                colors: ["#393", "#f8a31f", "#80699B", "#368ee0"],
                legend: {
                    x: 8,
                    y: 0,
                    borderWidth: 0,
                    align: "center",
                    verticalAlign: "bottom",
                    symbolWidth: 20,
                    symbolPadding: 3,
                    itemStyle: {
                        color: "#333"
                    },
                    labelFormatter: function() {
                        return "<strong>" + this.name + '</strong><span style="color:#BBB">(点击隐藏)</span>'
                    }
                },
                exporting: {
                    enabled: !1
                },
                credits: {
                    enabled: !1
                }
            };
            Highcharts.setOptions({
                global: {
                    useUTC: !1
                }
            }),
            e.chart.renderTo = a,
            e.series = c,
            e = b.extend(!0, {},
            e, d),
            c[0].data[c[0].data.length - 1][0] - c[0].data[0][0] >= 864e5 && (e.xAxis.dateTimeLabelFormats.hour = "%m - %e");
            new Highcharts.Chart(e)
        },
        pie: function(a, c, d, e) {
            d || (d = "");
            var f = {
                chart: {
                    defaultSeriesType: "pie",
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: !1
                },
                title: {
                    text: "",
                    verticalAlign: "bottom",
                    y: 8,
                    style: {
                        color: "#333",
                        fontSize: "12px",
                        fontWeight: "bold"
                    }
                },
                tooltip: {
                    enabled: !0,
                    formatter: function() {
                        return '<b style="font-size:12px;">' + this.point.name + "</b>: " + this.percentage + " %"
                    }
                },
                series: "",
                legend: {
                    x: 8,
                    y: 0,
                    borderWidth: 0,
                    align: "center",
                    verticalAlign: "bottom",
                    symbolWidth: 20,
                    symbolPadding: 3,
                    itemStyle: {
                        color: "#333"
                    },
                    labelFormatter: function() {
                        return "<strong>" + this.name + "</strong>"
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: !0,
                        cursor: "pointer",
                        showCheckbox: !0,
                        dataLabels: {
                            enabled: !0,
                            color: "#999",
                            connectorColor: "#CCC",
                            formatter: function() {
                                return '<b style="font-size:12px;">' + this.point.name + "</b>"
                            }
                        },
                        shadow: !1,
                        size: 80,
                        showInLegend: !1
                    }
                },
                colors: ["#F7BA00", "#0099FF", "#00CC00", "#FF5522", "#FF70B7", "#119999"],
                exporting: {
                    enabled: !1
                },
                credits: {
                    enabled: !1
                }
            };
            Highcharts.setOptions({
                global: {
                    useUTC: !1
                }
            }),
            f.chart.renderTo = a,
            f.title.text = d,
            f.series = c,
            f = b.extend(!0, {},
            f, e);
            new Highcharts.Chart(f)
        },
        column: function(a, c, d, e) {
            var f = {
                chart: {
                    type: "column"
                },
                title: "",
                xAxis: {
                    title: {
                        text: null
                    },
                    lineWidth: 1,
                    lineColor: "#EEE",
                    labels: {
                        y: 18,
                        style: {
                            fontSize: 12
                        }
                    }
                },
                yAxis: {
                    min: 0,
                    title: "",
                    gridLineColor: "#EEE",
                    gridLineWidth: 1,
                    endOnTick: !1
                },
                legend: {
                    align: "center",
                    verticalAlign: "bottom",
                    x: 0,
                    y: 6,
                    borderWidth: 0,
                    backgroundColor: "#FFFFFF",
                    shadow: !1,
                    itemStyle: {
                        color: "#333"
                    },
                    labelFormatter: function() {
                        return "<strong>" + this.name + '</strong><span style="color:#BBB">(点击隐藏)</span>'
                    }
                },
                tooltip: {
                    shadow: !1,
                    borderWidth: 0,
                    backgroundColor: "rgba(0,0, 0, .85)",
                    style: {
                        color: "#FFF",
                        fontSize: "12px"
                    },
                    formatter: function() {
                        return "" + this.series.name + ": " + this.y + this.series.options.unit
                    }
                },
                plotOptions: {
                    column: {
                        dataLabels: {
                            enabled: !1
                        },
                        shadow: !1
                    },
                    series: {
                        colorByPoint: !1,
                        animation: !1,
                        pointWidth: 12
                    }
                },
                colors: ["#0099FF", "#F7BA00", "#00CC00"],
                exporting: {
                    enabled: !1
                },
                credits: {
                    enabled: !1
                }
            };
            Highcharts.setOptions({
                global: {
                    useUTC: !1
                }
            }),
            f.chart.renderTo = a,
            f.xAxis.categories = c,
            f.series = d,
            f = b.extend(!0, {},
            f, e);
            new Highcharts.Chart(f)
        }
    }
});