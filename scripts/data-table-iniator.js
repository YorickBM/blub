$(document).ready(function () {
    const tables = $("table.wp-block-yorickblom-data-table");
    for(var i = 0; i < tables.length; i++) {
        const id = $(tables[i]).attr('id');
        const url = $(tables[i]).data('url');
        const json = $(tables[i]).data('json');
        const cols = $(tables[i]).data('cols').split(',');
        
        var ajax = {
            url: url,
            type: "GET"
        }
        if(json.toString().length >= 2) {
            ajax.type = "POST";
            ajax.data = json;
        }

        var columns = [];
        cols.forEach(element => {
            columns.push({ data: element.toLowerCase() });
        });
        console.log(columns);

        new DataTable('#'+id, {
            "dom": '<"dt-buttons"Bf><"clear">irtp',
            ajax: ajax,
            columns: columns,
            responsive: true,
            "buttons": [
				'colvis',
				'copyHtml5',
                'csvHtml5',
				'excelHtml5',
                'pdfHtml5',
				'print'
			]
        });
    }
});