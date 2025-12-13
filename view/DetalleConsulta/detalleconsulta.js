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

    // Validación
    // if (!prompt.trim()) {
    //     Swal.fire("Advertencia", "No puedes enviar un mensaje vacío", "warning");
    //     return;
    // }

    // -------- 1. GUARDAR MENSAJE DEL USUARIO --------
    var formData = new FormData();
    formData.append('cons_id', cons_id);
    formData.append('usu_id', usu_id);
    formData.append('det_contenido', prompt);

    let files = $("#fileElem")[0].files;
    for (let i = 0; i < files.length; i++) {
        formData.append("files[]", files[i]);
    }

    $('#btnenviar').prop("disabled", true);
    $('#btnenviar').html('<i class="fa fa-spinner fa-spin"></i> Enviando...');

    $.ajax({
        url: "../../controller/consulta.php?op=insertdetalle",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function () {

            mostrar(cons_id); // Recarga chat del usuario

            // -------- 2. OBTENER HISTORIAL --------
            $.post(
                "../../controller/consulta.php?op=obtener_historial",
                { cons_id: cons_id },
                function (historialRaw) {

                    let historial = JSON.parse(historialRaw);
                    let mensajes = historial.map(row => ({
                        role: (row.usu_id == 2 ? "model" : "user"),
                        parts: [{ text: row.det_contenido }]
                    }));

                    // -------- 4. AGREGAR EL ÚLTIMO MENSAJE DEL USUARIO --------
                    mensajes.push({
                        role: "user",
                        parts: [{ text: prompt }]
                    });
                    //console.log("Historial enviado a Gemini:", mensajes);

                    // -------- 5. ENVIAR A GEMINI --------
                    if (files.length > 0) {
                        let uploadData = new FormData();
                        for (let i = 0; i < files.length; i++) uploadData.append("files[]", files[i]);

                        $.ajax({
                            url: "../../controller/consulta.php?op=subir_archivos_cloud",
                            type: "POST",
                            data: uploadData,
                            processData: false,
                            contentType: false,
                            success: function (uploadedURIsRaw) {
                                console.log("RESPONSE:", uploadedURIsRaw);
                                console.log("response.urisAdjuntos:", uploadedURIsRaw.urisAdjuntos);

                                let urisAdjuntos = JSON.parse(uploadedURIsRaw);

                                // Añadir cada archivo como parte separada (role user + fileUri)
                                urisAdjuntos.forEach(uri => {
                                    mensajes.push({
                                        role: "user",
                                        parts: [{ fileUri: uri, mimeType: "application/pdf" }]
                                    });
                                });

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
                        // No hay archivos → enviamos historial inmediatamente
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

// ---------- Función que envía a Gemini y guarda la respuesta ----------
function enviarAGeminiYGuardar(mensajes, cons_id) {

    // LOG (opcional)
    console.log("Enviar a Gemini -> mensajes:", mensajes);

    $.post("../../controller/consulta.php?op=ai_prompt",
        { mensajes: JSON.stringify(mensajes) },
        function (response) {
            console.log("Gemini respondió:", response);

            try {
                var json = JSON.parse(response);
                var respuestaIA = json.candidates && json.candidates[0].content.parts[0].text
                    ? json.candidates[0].content.parts[0].text
                    : JSON.stringify(json);

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