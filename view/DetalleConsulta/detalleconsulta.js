function init() {

}


$(document).ready(function() {
    const params = new URLSearchParams(window.location.search);
    const cons_id = params.get("ID");
    //console.log(cons_id);
    
    mostrar(cons_id);
});

$("#btnenviar").on("click", function () {
    const params = new URLSearchParams(window.location.search);
    const cons_id = params.get("ID");
    var usu_id = $('#user_idx').val();
    var prompt = $('#prompt').val();

    var formData = new FormData();
    formData.append('cons_id', cons_id);
    formData.append('usu_id', usu_id);
    formData.append('det_contenido', prompt);


    // if ($('#prompt').summernote('isEmpty')) {
    //     swal("¡Advertencia!", "No puedes dejar el prompt vacío", "warning");
    // } else {

        var totalFiles = $('#fileElem').val().length;
        for(var i = 0; i<totalFiles; i++){
            formData.append("files[]", $('#fileElem')[0].files[i]);
        }

        $('#btnenviar').prop("disabled",true);
        $('#btnenviar').html('<i class="fa fa-spinner fa-spin"></i> Enviando...');
        $.ajax({
            url: "../../controller/consulta.php?op=insertdetalle",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(data){

                // $('#fileElem').val('');
                //$('#prompt').summernote('reset');
                console.log(data);

                mostrar(cons_id);
                $('#btnenviar').prop("disabled",false);
                $('#btnenviar').html('Enviar');  
                
            }
        });
        $.post("../../controller/consulta.php?op=ai_prompt",
            { prompt: prompt },
            function (response) {
    
                console.log("Gemini dijo:", response);
                mostrar(cons_id);
                var json = JSON.parse(response);
                var respuestaIA = json.candidates[0].content.parts[0].text;

                // ---- GUARDAR RESPUESTA EN BD ----
                $.post(
                    "../../controller/consulta.php?op=insertdetalle",
                    {
                        cons_id: cons_id,
                        usu_id: 2, // ID fijo para Gemini
                        det_contenido: respuestaIA
                    },
                    function (result) {
                        console.log("IA guardada en BD:", result);
                    }
                );
                

                Swal.fire({
                    title: "Respuesta de Gemini",
                    html: `<pre>${response}</pre>`,
                    width: 600,
                });
            }
        );


    // }

});


function mostrar(id) {

    $.post("../../controller/consulta.php?op=listardetalle", {cons_id: id}, function (data){
        //console.log("Respuesta del detalle:", data);
        $('#lbldetalle').html(data);
        scrollToBottom();
    });

    $.post("../../controller/consulta.php?op=mostrar", {cons_id: id}, function (data) {
        data = JSON.parse(data);

        console.log(data);

        $('#lblnomconsulta').html("Consulta: " + data.cons_nom);
    });
}

function scrollToBottom() {
    window.scrollTo({
        top: document.body.scrollHeight,
        behavior: 'smooth'
    });
}

init();