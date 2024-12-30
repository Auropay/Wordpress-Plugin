var main_chart;
jQuery(function () {
	var order_data = JSON.parse(decodeURIComponent(report_vars.chart_data));
	var drawGraph = function (highlight, type = "line") {
		type = jQuery("#chart_type").val();
		if (highlight == "sale") {
			if (type == "bar") {
				var series = [
					{
						label: report_vars.gross_sale_amount,
						data: order_data.sale_amount,
						yaxis: 1,
						color: report_vars.chart_colours.sales_amount,
						bars: {
							fillColor: report_vars.chart_colours.sales_amount,
							fill: true,
							show: true,
							lineWidth: 0,
							barWidth: report_vars.barwidth * 0.5,
							align: "center",
						},
						shadowSize: 0,
						enable_tooltip: true,
						stack: true,
					},
				];
			} else {
				var series = [
					{
						label: "Gross sales amount",
						data: order_data.sale_amount,
						yaxis: 1,
						color: report_vars.chart_colours.sales_amount,
						points: {
							show: true,
							radius: 5,
							lineWidth: 2,
							fillColor: "#fff",
							fill: true,
						},
						lines: {
							show: true,
							lineWidth: 2,
							fill: false,
						},
						shadowSize: 0,
						enable_tooltip: true,
					},
				];
			}
		} else if (highlight == "refunded") {
			if (type == "bar") {
				var series = [
					{
						label: report_vars.refund_amount,
						data: order_data.refund_amount,
						yaxis: 1,
						color: report_vars.chart_colours.refund_amount,
						bars: {
							fillColor: report_vars.chart_colours.refund_amount,
							fill: true,
							show: true,
							lineWidth: 0,
							barWidth: report_vars.barwidth * 0.5,
							align: "center",
						},
						shadowSize: 0,
						enable_tooltip: true,
						stack: true,
						append_tooltip: report_vars.refunds,
					},
				];
			} else {
				var series = [
					{
						label: report_vars.refund_amount,
						data: order_data.refund_amount,
						yaxis: 1,
						color: report_vars.chart_colours.refund_amount,
						points: {
							show: true,
							radius: 5,
							lineWidth: 2,
							fillColor: "#fff",
							fill: true,
						},
						lines: {
							show: true,
							lineWidth: 2,
							fill: false,
						},
						shadowSize: 0,
						enable_tooltip: true,
						append_tooltip: report_vars.refunds,
					},
				];
			}
		} else if (highlight == "failed") {
			if (type == "bar") {
				var series = [
					{
						label: report_vars.failed_amount,
						data: order_data.failed_amount,
						yaxis: 1,
						color: report_vars.chart_colours.failed_amount,
						bars: {
							fillColor: report_vars.chart_colours.failed_amount,
							fill: true,
							show: true,
							lineWidth: 0,
							barWidth: report_vars.barwidth * 0.5,
							align: "center",
						},
						stack: true,
						shadowSize: 0,
						enable_tooltip: true,
						append_tooltip: report_vars.failed,
					},
				];
			} else {
				var series = [
					{
						label: report_vars.failed_amount,
						data: order_data.failed_amount,
						yaxis: 1,
						color: report_vars.chart_colours.failed_amount,
						points: {
							show: true,
							radius: 5,
							lineWidth: 2,
							fillColor: "#fff",
							fill: true,
						},
						lines: {
							show: true,
							lineWidth: 2,
							fill: false,
						},
						shadowSize: 0,
						enable_tooltip: true,
						append_tooltip: report_vars.failed,
					},
				];
			}
		} else {
			if (type == "bar") {
				var series = [
					{
						label: report_vars.gross_sale_amount,
						data: order_data.sale_amount,
						yaxis: 1,
						color: report_vars.chart_colours.sales_amount,
						bars: {
							fillColor: report_vars.chart_colours.sales_amount,
							fill: true,
							show: true,
							lineWidth: 0,
							barWidth: report_vars.barwidth * 0.5,
							align: "center",
						},
						shadowSize: 0,
						stack: true,
						enable_tooltip: true,
						append_tooltip: report_vars.sales,
					},
					{
						label: report_vars.refund_amount,
						data: order_data.refund_amount,
						yaxis: 1,
						color: report_vars.chart_colours.refund_amount,
						bars: {
							fillColor: report_vars.chart_colours.refund_amount,
							fill: true,
							show: true,
							lineWidth: 0,
							barWidth: report_vars.barwidth * 0.5,
							align: "center",
						},
						shadowSize: 0,
						stack: true,
						enable_tooltip: true,
						append_tooltip: report_vars.refunds,
					},
					{
						label: report_vars.failed_amount,
						data: order_data.failed_amount,
						yaxis: 1,
						color: report_vars.chart_colours.failed_amount,
						bars: {
							fillColor: report_vars.chart_colours.failed_amount,
							fill: true,
							show: true,
							lineWidth: 0,
							barWidth: report_vars.barwidth * 0.5,
							align: "center",
						},
						stack: true,
						shadowSize: 0,
						enable_tooltip: true,
						append_tooltip: report_vars.failed,
					},
				];
			} else {
				var series = [
					{
						label: report_vars.gross_sale_amount,
						data: order_data.sale_amount,
						yaxis: 1,
						color: report_vars.chart_colours.sales_amount,
						points: {
							show: true,
							radius: 5,
							lineWidth: 2,
							fillColor: "#fff",
							fill: true,
						},
						lines: {
							show: true,
							lineWidth: 2,
							fill: false,
						},
						shadowSize: 0,
						enable_tooltip: true,
						append_tooltip: report_vars.sales,
					},
					{
						label: report_vars.refund_amount,
						data: order_data.refund_amount,
						yaxis: 1,
						color: report_vars.chart_colours.refund_amount,
						points: {
							show: true,
							radius: 5,
							lineWidth: 2,
							fillColor: "#fff",
							fill: true,
						},
						lines: {
							show: true,
							lineWidth: 2,
							fill: false,
						},
						shadowSize: 0,
						enable_tooltip: true,
						append_tooltip: report_vars.refunds,
					},
					{
						label: report_vars.failed_amount,
						data: order_data.failed_amount,
						yaxis: 1,
						color: report_vars.chart_colours.failed_amount,
						points: {
							show: true,
							radius: 5,
							lineWidth: 2,
							fillColor: "#fff",
							fill: true,
						},
						lines: {
							show: true,
							lineWidth: 2,
							fill: false,
						},
						shadowSize: 0,
						enable_tooltip: true,
						append_tooltip: report_vars.failed,
					},
				];
			}
		}

		if (highlight !== "undefined" && series[highlight]) {
			highlight_series = series[highlight];

			highlight_series.color = "#9c5d90";

			if (highlight_series.bars) {
				highlight_series.bars.fillColor = "#9c5d90";
			}

			if (highlight_series.lines) {
				highlight_series.lines.lineWidth = 5;
			}
		}

		main_chart = jQuery.plot(jQuery(".chart-placeholder.main"), series, {
			legend: {
				show: false,
			},
			grid: {
				color: "#aaa",
				borderColor: "transparent",
				borderWidth: 0,
				hoverable: true,
			},
			xaxes: [
				{
					color: "#aaa",
					position: "bottom",
					tickColor: "transparent",
					mode: "time",
					timeformat: report_vars.chart_groupby === "day" ? "%d %b" : "%b",
					monthNames: JSON.parse(
						decodeURIComponent(report_vars.jsonMonthAbbrev)
					),
					tickLength: 1,
					minTickSize: [1, report_vars.chart_groupby],
					font: {
						color: "#aaa",
					},
				},
			],
			yaxes: [
				{
					min: 0,
					minTickSize: 1,
					tickDecimals: 2,
					color: "#d4d9dc",
					font: {
						color: "#aaa",
					},
					tickFormatter: function (v, axis) {
						return "₹" + v.toFixed(axis.tickDecimals);
					},
				},
				{
					position: "right",
					min: 0,
					tickDecimals: 2,
					alignTicksWithAxis: 1,
					color: "transparent",
					font: {
						color: "#aaa",
					},
				},
			],
		});
		jQuery(".chart-placeholder").resize();
	};

	drawGraph("sale");
	jQuery(".highlight_series").hover(
		function () {
			drawGraph(jQuery(this).data("series"));
		},
		function () {
			drawGraph();
		}
	);

	jQuery("#sale_box").hover(function () {
		jQuery(this).css("cursor", "pointer");
		jQuery(this).css("background-color", "#f0f0f1");
		jQuery("#refunded_box").css("background-color", "#fff");
		jQuery("#failed_box").css("background-color", "#fff");
	});
	jQuery("#refunded_box").hover(function () {
		jQuery(this).css("cursor", "pointer");
		jQuery(this).css("background-color", "#f0f0f1");
		jQuery("#failed_box").css("background-color", "#fff");
		jQuery("#sale_box").css("background-color", "#fff");
	});
	jQuery("#failed_box").hover(function () {
		jQuery(this).css("cursor", "pointer");
		jQuery(this).css("background-color", "#f0f0f1");
		jQuery("#refunded_box").css("background-color", "#fff");
		jQuery("#sale_box").css("background-color", "#fff");
	});

	jQuery("#sale_box").click(function () {
		drawGraph("sale");
		jQuery("#data_type").val("sale");
		jQuery("#summary_box_type").html("Sales");
		jQuery("#sales_stat_details").show();
		jQuery("#refunded-stat-details").hide();
		jQuery("#failed-stat-details").hide();
	});

	jQuery("#failed_box").click(function () {
		drawGraph("failed");
		jQuery("#data_type").val("failed");
		jQuery("#summary_box_type").html("Failed");
		jQuery("#sales_stat_details").hide();
		jQuery("#refunded-stat-details").hide();
		jQuery("#failed-stat-details").show();
	});

	jQuery("#refunded_box").click(function () {
		drawGraph("refunded");
		jQuery("#data_type").val("refunded");
		jQuery("#summary_box_type").html("Refunded");
		jQuery("#sales_stat_details").hide();
		jQuery("#failed-stat-details").hide();
		jQuery("#refunded-stat-details").show();
	});

	jQuery("#line_chart").click(function () {
		jQuery("#chart_type").val("line");
		drawGraph(jQuery("#data_type").val());
		jQuery("#line_chart").prop("disabled", true);
		jQuery("#line_chart").css("background-color", "gray");
		jQuery("#bar_chart").css("background-color", "");
		jQuery("#bar_chart").prop("disabled", false);
	});
	jQuery("#bar_chart").click(function () {
		jQuery("#chart_type").val("bar");
		drawGraph(jQuery("#data_type").val());
		jQuery("#line_chart").prop("disabled", false);
		jQuery("#bar_chart").prop("disabled", true);
		jQuery("#bar_chart").css("background-color", "gray");
		jQuery("#line_chart").css("background-color", "");
	});
	jQuery("#custom").click(function () {
		jQuery("#custom-box").show();
		jQuery("#custom").addClass("active");
		jQuery(".odate_range").removeClass("active");
	});

	var current_range = report_vars.current_range;
	jQuery(".custom").hide();
	// Show it only if the current range is 'custom'
	if (current_range === "custom") {
		jQuery(".custom").show();
	}

	jQuery("#from_datepicker").datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: "dd-mm-yy",
		maxDate: 0,
		onSelect: function (selected) {
			jQuery("#to_datepicker").datepicker("option", "minDate", selected);
		},
	});

	jQuery("#to_datepicker").datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: "dd-mm-yy",
		maxDate: 0,
		onSelect: function (selected) {
			jQuery("#from_datepicker").datepicker("option", "maxDate", selected);
		},
	});
});
