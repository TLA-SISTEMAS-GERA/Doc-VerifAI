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

    // 1 GUARDAR MENSAJE DEL USUARIO
    var formData = new FormData();
    formData.append('cons_id', cons_id);
    formData.append('usu_id', usu_id);
    formData.append('det_contenido', prompt);

    let files = $("#fileElem")[0].files;
    for (let i = 0; i < files.length; i++) {
        console.log(files[i].name);
        formData.append("files[]", files[i]);
    }

    $('#btnenviar').prop("disabled", true);
    $('#btnenviar').html('<i class="fa fa-spinner fa-spin"></i> Enviando...');

    $.ajax({
        //INSERTAR EL DETALLE/MENSAJE EN LA BDD
        url: "../../controller/consulta.php?op=insertdetalle",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data) {

            console.log(data);

            mostrar(cons_id); // Recarga chat del usuario
            // 2 OBTENER HISTORIAL DE LOS MENSAJES
            $.post(
                "../../controller/consulta.php?op=obtener_historial",
                { cons_id: cons_id },
                function (historialRaw) {
                    
                    //DETECTAR SI EL MENSAJE DEL HISTORIAL EL USUARIO 2 O DIFERENTE
                    //SI EL USUARIO ES USU_ID = 2 ENTONCES EL ROL ES MODEL (GEMINI) SI NO, ES UN USUARIO
                    let historial = JSON.parse(historialRaw);
                    let mensajes = historial.map(row => ({
                        role: (row.usu_id == 2 ? "model" : "user"),
                        parts: [{ text: row.det_contenido }]
                    }));

                    //VERIFICA SI HAY ARCHIVOS
                    if (files.length > 0) {
                        let uploadData = new FormData();

                        uploadData.append("cons_id", cons_id);
                        
                        for (let i = 0; i < files.length; i++) uploadData.append("files[]", files[i]);

                        $.ajax({
                            url: "../../controller/consulta.php?op=subir_archivos_cloud",
                            type: "POST",
                            data: uploadData,
                            processData: false,
                            contentType: false,
                            success: function (uploadedURIsRaw) {
                                
                                let resp = JSON.parse(uploadedURIsRaw);
                                
                                console.log("RESPONSE:", resp);
                                // FORMACION DEL MENSAJE Se agrega cada parte de la solicitud a gemini
                                let partes = [];
                                
                                //Agregar texto al prompt
                                partes.push({
                                    text: `Analiza el/los documentos adjuntos y responde claramente a la siguiente solicitud:\n\n${prompt}`
                                });

                                //   AQUI SE AGREGA OBTENER ARCHIVOS INFO DE consulta.php

                                // Agregar archivos (file_id)
                                resp.forEach(a => {
                                    partes.push({
                                        file_data: {
                                            mime_type: "application/pdf",
                                            file_uri: `https://generativelanguage.googleapis.com/v1beta/${a.file_id}`
                                        }
                                    });
                                });

                                mensajes.push({
                                    role: "user",
                                    parts: partes
                                })

                                // 4) Enviar a Gemini con historial+archivos+prompt
                                enviarAGeminiYGuardar(mensajes, cons_id);
                            },
                            error: function(err){
                                console.error("Error subiendo archivos:", err);
                                // aún así intentamos enviar historial sin archivos
                                enviarAGeminiYGuardar(mensajes, cons_id);
                            }
                        });

                    } else {
                        // No hay archivos → enviamos historial + prompt inmediatamente
                        enviarAGeminiYGuardar(mensajes, cons_id);
                    }
                }
            );
            $('#btnenviar').prop("disabled", false);
            $('#btnenviar').html('Enviar');
            $('#prompt').val('');
        }
    });

});

//Funcion que envia a Gemini y guarda la respuesta
// mensajes = Archivos + Prompt
// cons_id = id de la consulta a la que se enviara mensajes
function enviarAGeminiYGuardar(mensajes, cons_id) {

    $.post("../../controller/consulta.php?op=ai_prompt",
        { mensajes: JSON.stringify(mensajes) },
        function (response) {
            console.log("Gemini respondió:", response);

            try { 
                var json = JSON.parse(response);
                if (
                    json.candidates &&
                    json.candidates.length > 0 &&
                    json.candidates[0].content &&
                    json.candidates[0].content.parts &&
                    json.candidates[0].content.parts.length > 0
                ) {
                    //OBTENGO LO QUE ME DEVUELVE GEMINI
                    const textoPart = json.candidates[0].content.parts.find(p => p.text);
                    if (textoPart) {
                        respuestaIA = textoPart.text;
                    }
                }

                // Guardar la respuesta IA en BD (usu_id = 2)
                $.post("../../controller/consulta.php?op=insertdetalle",
                    {
                        cons_id: cons_id,
                        usu_id: 2,
                        det_contenido: respuestaIA
                    },
                    function () {
                        mostrar(cons_id); // refrescar chat
                    }
                );

            } catch (e) {
                console.error("Error parseando respuesta Gemini:", e, response);
            }
        }
    );
}

function mostrar(id) {

    $.post("../../controller/consulta.php?op=listardetalle", {cons_id: id}, function (data){
        //console.log("Respuesta del detalle:", data);
        $('#lbldetalle').html(data);

        // Ahora buscamos todos los mensajes del contenido
        $('#lbldetalle p').each(function () {

            
            let raw = $(this).text().trim(); // Obtener texto plano del mensaje
            let html = marked.parse(raw);    // Convertir Markdown → HTML
            let cleanHtml = DOMPurify.sanitize(html); // Seguridad
            
            $(this).html(cleanHtml); // Reemplazar texto por HTML renderizado
        });
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