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

$(document).on("click",".btn-inline","url-inline",function(){
    const realId = $(this).data("real-id");

    const ciphertext = $(this).attr("id");

    console.log(ciphertext);
    //DATO TEMPORAL (MIENTRAS EL USUARIO ENTRE A LA CONSULTA, EL VALOR EXISTIRÁ)
    sessionStorage.setItem("id_real", realId);                              
    
    //window.open('http://localhost:80/Doc-VerifAI/view/DetalleConsulta/?ID='+ciphertext+'');
    window.open('http://doc-verifai.tecnologisticaaduanal.com/view/DetalleConsulta/?ID='+ciphertext+'');
    
});

$('.nav-link').on('click', function () {
    let id = $(this).attr('id');
    
    if (id === 'pestConsultas') {
        cargarConsultas();
    }

    if (id === 'pestPapelera') {
        cargarPapelera();
    }
});

//  ---------------------->      FUNCIONES       <----------------------

//Mover consulta a papelera
function papelera(cons_id){
    console.log("Funcion Eliminar Papelera");
    console.log(cons_id);
    swal(
        {
            title: "¿Transferir esta consulta a la Papelera?",
            text: "Esta consulta se transferira a la pestaña Papelera",
            type: "error",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Si",
            cancelButtonText: "No",
            closeOnConfirm: false,
            
        },
        function(isConfirm) {
            if (isConfirm) {
                $.post("../../controller/consulta.php?op=delete_consulta_p", {cons_id: cons_id}, function (data){
                });

                $('#cons_data').DataTable().ajax.reload();
                
                swal({
                    title: "TLA DocVerifAI",
                    text: "Consulta Transferida a la Papelera.",
                    type: "success",
                    confirmButtonClass: "btn-success"
                });
            }
        }
    );
}

//Eliminar consulta
function eliminar(cons_id){
    console.log("Funcion Eliminar");
    //console.log(object);
    console.log(cons_id);
    swal(
        {
            title: "¿Eliminar esta Consulta?",
            text: "Esta consulta se eliminara, por lo tanto los cambios son irreversibles",
            type: "error",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Si",
            cancelButtonText: "No",
            closeOnConfirm: false,
            
        },
        function(isConfirm) {
            if (isConfirm) {
                
                $.ajax({
                    url: "../../controller/consulta.php?op=eliminar_bucket",
                    type: "POST",
                    data: { cons_id: cons_id },
                    success: function (data) {
                        //actualizarBarra(55, "Archivos procesados");
                        $.post("../../controller/consulta.php?op=delete_consulta", { cons_id: cons_id }, function (data){
                        });
                        console.log("Bucket eliminado ", data);                          
        
                    },
                    error: function(err){
                        console.error("Error al eliminar el bucket:", err);
                        // aún así intentamos enviar historial sin archivos
                        //enviarAGeminiYGuardar(mensajes, cons_id);
                    }
                });

                $('#cons_data').DataTable().ajax.reload();

                swal({
                    title: "TLA DocVerifAI",
                    text: "Consulta Eliminada.",
                    type: "success",
                    confirmButtonClass: "btn-success"
                });
            }
        }
    );
}

//Listar las consultas Activas
function cargarConsultas() { 

    // if ($.fn.DataTable.isDataTable('#cons_data')) {
    //     $('#cons_data').DataTable().clear().destroy();
    // }
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
}

//Listar las consultas enviadas a la Papelera
function cargarPapelera() {

    // if ($.fn.DataTable.isDataTable('#cons_data')) {
    //     $('#cons_data').DataTable().clear().destroy();
    // }

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
            url: '../../controller/consulta.php?op=listar_consultas_papelera',
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
            "sZeroRecords":    "No se encontraron consultas en la papelera",
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
}

