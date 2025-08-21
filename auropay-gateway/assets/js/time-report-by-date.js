let main_chart;

jQuery(function () {
  const order_data = auropay_report_vars.chart_data;
  const ctx = document.getElementById('main_chart_canvas').getContext('2d');
  // Convert timestamp to readable date
  const formatDate = ts => {
	  const d = new Date(ts);
	  if (auropay_report_vars.current_range === 'year') {
		  return d.toLocaleDateString('en-GB', {
			  month: 'short',
			  year: 'numeric'
		  });
	  } else {
		  return d.toLocaleDateString('en-GB', {
			  day: '2-digit',
			  month: 'short',
			  year: 'numeric'
		  });
	  }
  };

  // Create labels dynamically from timestamps in sale_amount
  const labels = order_data.sale_amount.map(item => formatDate(item[0]));

  // Helper to extract just the amounts from [timestamp, amount]
  const extractAmounts = arr => arr.map(item => item[1]);

  const getDatasets = (highlight = '', type = 'line') => {
    const c = auropay_report_vars.chart_colours;
    const datasetOpts = (label, data, color, isHighlight) => ({
      label,
      data: extractAmounts(data),
      backgroundColor: type === 'bar' ? color : 'transparent',
      borderColor: color,
      borderWidth: isHighlight ? 3 : 2,
      pointBackgroundColor: color,
      pointRadius: isHighlight ? 6 : 4,
      type,
      fill: false
    });

    const datasets = {
      sale: datasetOpts(auropay_report_vars.gross_sale_amount, order_data.sale_amount, c.sales_color, highlight === 'sale'),
      refunded: datasetOpts(auropay_report_vars.refund_amount, order_data.refund_amount, c.refund_color, highlight === 'refunded'),
      failed: datasetOpts(auropay_report_vars.failed_amount, order_data.failed_amount, c.failed_color, highlight === 'failed')
    };

    return highlight && datasets[highlight] ? [datasets[highlight]] : Object.values(datasets);
  };

  const drawGraph = (highlight = 'sale') => {
    const chartType = jQuery('#chart_type').val();
    const datasets = getDatasets(highlight, chartType);

    if (main_chart) main_chart.destroy();
    main_chart = new Chart(ctx, {
      type: chartType,
      data: {
        labels: labels,
        datasets: datasets
      },
      options: {
        responsive: true,
        scales: {
          x: {
            ticks: { color: '#aaa' },
            title: { display: false, text: 'Date' }
          },
          y: {
            beginAtZero: true,
            ticks: {
              color: '#aaa',
              callback: value => `₹${value}`
            }
          }
        },
        plugins: {
          legend: { display: false },
          tooltip: { mode: 'index', intersect: false }
        }
      }
    });
  };

  drawGraph('sale');

  jQuery('.highlight_series').hover(
    function () { drawGraph(jQuery(this).data('series')); },
    function () { drawGraph(); }
  );

  ['sale', 'refunded', 'failed'].forEach(type => {
    const box = `#${type}_box`;
    jQuery(box).hover(() => {
      jQuery(box).css({ cursor: 'pointer', 'background-color': '#f0f0f1' });
      ['sale', 'refunded', 'failed'].filter(i => i !== type).forEach(i => jQuery(`#${i}_box`).css('background-color', '#fff'));
    });
    jQuery(box).click(() => {
      drawGraph(type);
      jQuery('#data_type').val(type);
      jQuery('#summary_box_type').html(type.charAt(0).toUpperCase() + type.slice(1));
      ['sales_stat_details', 'refunded-stat-details', 'failed-stat-details'].forEach(id => jQuery(`#${id}`).hide());
      jQuery(`#${type === 'sale' ? 'sales_stat_details' : type + '-stat-details'}`).show();
    });
  });

  jQuery('#line_chart, #bar_chart').click(function () {
    const type = this.id === 'line_chart' ? 'line' : 'bar';
    jQuery('#chart_type').val(type);
    drawGraph(jQuery('#data_type').val());
    jQuery('#line_chart').prop('disabled', type === 'line').css('background-color', type === 'line' ? 'gray' : '');
    jQuery('#bar_chart').prop('disabled', type === 'bar').css('background-color', type === 'bar' ? 'gray' : '');
  });

  jQuery('#custom').click(function () {
    jQuery('#custom-box').show();
    jQuery('#custom').addClass('active');
    jQuery('.odate_range').removeClass('active');
  });

  if (auropay_report_vars.current_range === 'custom') jQuery('.custom').show();

  jQuery('#from_datepicker').datepicker({
    changeMonth: true, changeYear: true, dateFormat: 'dd-mm-yy', maxDate: 0,
    onSelect: selected => jQuery('#to_datepicker').datepicker('option', 'minDate', selected)
  });

  jQuery('#to_datepicker').datepicker({
    changeMonth: true, changeYear: true, dateFormat: 'dd-mm-yy', maxDate: 0,
    onSelect: selected => jQuery('#from_datepicker').datepicker('option', 'maxDate', selected)
  });
});
