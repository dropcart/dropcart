/*
// Custom made jQuery dynamic tables
// © Dropcart
*/

// define variables
var timeout;
var query;
var parameters;
var stringParameters;
var arrayParameters = {};
var isLoaded = 0;
var pagination = false;
var items = 0;
var pageNumber = 1;
var totalRows = 0;
var totalPages = 0;

jQuery.fn.removeAll = function() {
	this.each(function() {
		var newEl = this.cloneNode(false);
		this.parentNode.replaceChild(newEl, this);
	});
};

function loadTable(curThis, query, parameters) {
	
	var tableData	= '';
	var sort_column	= '';
	var sort_order	= '';
	paginationData = '';
	var json_file	= $(curThis).data('json-file');
	
	if(pageNumber) {
		offset = (pageNumber-1) * items;
		limit = items;
	}

	$(curThis).find('thead tr th').each(function(){
		
		if($(this).data('json-sort')) {
			sort_column	= $(this).data('json-column');
			sort_order	= $(this).data('json-sort');
		}
		
	});
	
	$(curThis).find('tbody').removeAll();

	$.ajax({
		type:		'GET',
		dataType:	'json',
		url:		'./includes/json/'+json_file+'?1'+parameters,
		data:
		{
			sort_column: sort_column,
			sort_order: sort_order,
			query: query,
			offset: offset,
			limit:limit
		},
		success: function(data, textStatus) {
		// Handle success
			
			if(data.totalRows == 0) {
				
				colspan = $(curThis).find('thead tr th').length;
				tableData = '<tr><td colspan="'+colspan+'">Geen resultaten gevonden</td></tr>';
				$('.pagination-json').html(paginationData);
				
			} else {
			
				$.each(data.details, function (key, row) {
					
					tableData += '<tr>';
	
					$(curThis).find('thead tr th').each(function(i){
						
						tableData += '<td>' + row[i] + '</td>';
						
					});
					
					tableData += '</tr>';
					
				});

				if(data.totalRows != totalRows) {

					pages = Math.ceil(data.totalRows / items);
					
					if(pages > 1) {
						paginationData += '<li><span class="btn-link" data-pagenumber="prev">&laquo;</span></li>';
					}
					
					for(i=1;i<=pages;i++) {
						paginationData += '<li><span class="btn-link" data-pagenumber="'+i+'">'+i+'</span></li>';
					}

					if(pages > 1) {

						paginationData += '<li><span class="btn-link" data-pagenumber="next">&raquo;</span></li>';
					}
					
					totalPages = i-1;
					
					$('.pagination-json').html(paginationData);
				
				}
				
			}
			
			totalRows = data.totalRows;

			$(curThis).find('tbody').append(tableData);
			$('.pagination span').parent().removeClass('active');
			$('.pagination span[data-pagenumber=prev], .pagination span[data-pagenumber=next]').parent().removeClass('disabled');
			$('.pagination span[data-pagenumber='+pageNumber+']').parent().addClass('active');
			
			if(pageNumber == 1) {
				$('.pagination span[data-pagenumber=prev]').parent().addClass('disabled');
			} else if(pageNumber == totalPages) {
				$('.pagination span[data-pagenumber=next]').parent().addClass('disabled');
			}
			
		
		},
		error: function(xhr, textStatus, errorThrown) {
			// Handle error
			colspan = $(curThis).find('thead tr th').length;
			tableData = '<tr><td colspan="'+colspan+'">Foutmelding: Kan resultaten niet laden</td></tr>';
			$(curThis).find('tbody').append(tableData);
			$('.pagination-json').html(paginationData);
			totalRows = 0;

		}
	});

}

function loadParameters(curThis){
	
	if($(curThis).parent().data('json-key')) {
		
		var key		= $(curThis).parent().data('json-key');
		var value	= $(curThis).data('json-value');
		var table	= $(curThis).parent().data('json-table');

	} else {
		
		var key		= $(curThis).data('json-key');
		var value	= $(curThis).find(':selected').data('json-value');
		var table	= $(curThis).data('json-table');

	}

	arrayParameters[key] = value;
		
	$(curThis).parent().find('[data-json-value]').each(function(){
		$(this).removeClass('active');
	});
	
	
	$(curThis).addClass('active');
	
	parameters = stringParameters;

	$.each(arrayParameters, function (key, value) {
		parameters += '&'+key+'='+value;
	});
	
	isLoaded = 1;
	clearTimeout(timeout);
	timeout = setTimeout(function(){
		loadTable(table, query, parameters)
	},50);

}

$('.table-json').each(function(){

	var curThis = $(this);
	stringParameters = $(this).data('json-parameters');
	parameters = stringParameters;
	
	$('.pagination-json').each(function(){
		
		if($(this).data('json-table')) {
			pagination = true;
			items = $(this).data('json-items');
		}
		
	});

	$(curThis).find('thead tr th[data-json-column]').click(function(){
				
		if($(this).data('json-sort') == 'asc') {
			order = 'desc';
		} else {
			order = 'asc';			
		}
		
		$(curThis).find('thead tr th').each(function(){
			$(this).data('json-sort', '');
			$(this).attr('data-json-sort', '');
		});
		
		$(this).data('json-sort', order);
		$(this).attr('data-json-sort', order);
		
		loadTable(curThis, query, parameters);
		
	});
		
});


$(document).on('keyup', '.search-json', function() {
	
	pageNumber = 1;
	query = $(this).val();
	var table = $(this).data('json-table');
	
	isLoaded = 1;
	
	clearTimeout(timeout);
	timeout = setTimeout(function(){
		loadTable(table, query, parameters)
	},200);
	
});

$(document).on('click', '.pagination-json li span', function() {
	
	if($(this).data('pagenumber') == 'prev') { //prev
		pageNumber = pageNumber - 1;
	} else if($(this).data('pagenumber') == 'next') { //next
		pageNumber = pageNumber + 1;
	} else {
		pageNumber = $(this).data('pagenumber');
	}
		
	var table = $(this).parent().parent().data('json-table');
	
	isLoaded = 1;
	
	clearTimeout(timeout);
	timeout = setTimeout(function(){
		loadTable(table, query, parameters)
	},50);
	
});

$('.active[data-json-value], :selected[data-json-value]').each(function(){

	var curThis = $(this);
	loadParameters(curThis);

});

$(document).on('click', '[data-json-value]', function() {
	pageNumber = 1;
	var curThis = $(this);
	loadParameters(curThis);
	
});

$(document).on('change', '[data-json-key]', function() {
	pageNumber = 1;
	var curThis = $(this);
	loadParameters(curThis);
	
});

if(isLoaded == 0) {
	loadTable(table, query, parameters);
}