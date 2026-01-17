var tabla;
var usu_id = $('#user_idx').val();

function init(){
    $("#ticket_form").on("submit",function(e){
        //guardar(e);
    });
}

$(document).ready(function() {
    $('#viewuser').hide();
    tabla=$('#cons_data').dataTable({ 
        "aProcessing": true,
        "aServerSide": true,
        dom: 'Bfrtip',
        "searching": true,
        lengthChange: false,
        colReorder: true,
        buttons: [		          
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pdfHtml5'
                ],
        "ajax":{
            url: '../../controller/consulta.php?op=listar_consultas',
            type : "post",
            dataType : "json",	
            data:{ usu_id : usu_id },						
            error: function(e){
                console.log(e.responseText);	
            }
        },
        "ordering": false,
        "bDestroy": true,
        "responsive": true,
        "bInfo":true,
        "iDisplayLength": 10,
        "autoWidth": false,
        "language": {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron consultas",
            "sEmptyTable":     "Ninguna consulta disponible en esta tabla",
            "sInfo":           "Mostrando un total de _TOTAL_ consultas",
            "sInfoEmpty":      "Mostrando un total de 0 consultas",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }     
    }).DataTable(); 
});

$(document).on("click",".btn-inline","url-inline", function(){
    const realId = $(this).data("real-id");

    const ciphertext = $(this).attr("id");

    console.log(ciphertext);
    //DATO TEMPORAL (MIENTRAS EL USUARIO ENTRE A LA CONSULTA, EL VALOR EXISTIRÁ)
    sessionStorage.setItem("id_real", realId); 
    
    //window.open('http://localhost:80/Doc-VerifAI/view/DetalleConsulta/?ID='+ciphertext+'');
    window.open('http://doc-verifai.tecnologisticaaduanal.com//view/DetalleConsulta/?ID='+ciphertext+'');
    
});