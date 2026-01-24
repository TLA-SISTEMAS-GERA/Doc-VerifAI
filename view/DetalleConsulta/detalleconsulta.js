function init() {

}

function mostrarBarra() {
    $("#barra_progreso").val(0).show();
}

function actualizarBarra(valor, texto = "") {
    $("#barra_progreso").val(valor);
    if (texto) {
        $("#barra_progreso").attr("title", texto);
    }
}

function ocultarBarra() {
    $("#barra_progreso").val(100);
    setTimeout(() => {
        $("#barra_progreso").hide().val(0);
    }, 500);
}



$(document).ready(function() {
    const params = new URLSearchParams(window.location.search);
    const cons_id = params.get("ID");
    //console.log(cons_id);
    
    $('#prompt').summernote({
        height: 100,
        lang: "es-ES",
        callbacks: {
            onImageUpload: function(image) {
                console.log("Image detect...");
                myimagetreat(image[0]);
            },
            onPaste: function (e) {
                console.log("Text detect...");
            }
        },
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']]
          ]
    
    });
    $('#tickd_descripusu').summernote({
        height: 250,
        lang: "es-ES",
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']]
          ]
    }); 
    mostrar(cons_id);
});

$("#btncargar").on("click", function () {
    const params = new URLSearchParams(window.location.search);
    const cons_id = params.get("ID");
    const decoded_id =  decodeURIComponent(cons_id);
    const encodedCiphertext = encodeURIComponent(cons_id);
    const id = decoded_id.replace(/\s/g, '+'); 

    var usu_id = $('#user_idx').val();
    var prompt = $('#prompt').val();
    var btnenviar = $('#btnenviar');

    var formData = new FormData();

    formData.append('cons_id', cons_id);
    formData.append('usu_id', usu_id);
    formData.append('det_contenido', prompt);

    let files = $("#fileElem")[0].files;
    for (let i = 0; i < files.length; i++) {
        console.log("Archivos encontrados"+files[i].name);
        formData.append("files[]", files[i]);
    }
    console.log(id);

    $.ajax({
        //INSERTO UN DETALLE DE CARGA DE ARCHIVOS SOLAMENTE
        url: "../../controller/consulta.php?op=insertdetalle",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function () {

            //Se muestra el detalle
            mostrar(cons_id);

            $('#btnenviar').removeAttr('disabled').addClass('btn btn-rounded btn-inline btn_primary');

            console.log("BOTON DE PROCESAR ACTIVADO");
            

            //SE RECORRE FILES DEL FORMDATA PARA SUBIRLOS UNO X UNO
            if (files.length > 0) {
                let uploadData = new FormData();
                //OBTENGO EL ID DE LA CONSULTA
                uploadData.append("cons_id", cons_id);
                for (let i = 0; i < files.length; i++) uploadData.append("files[]", files[i]);
        
                $.ajax({
                    url: "../../controller/consulta.php?op=subir_archivos_cloud",
                    type: "POST",
                    data: uploadData,
                    processData: false,
                    contentType: false,
                    success: function (uploadedURIsRaw) {
                        //actualizarBarra(55, "Archivos procesados");
                        console.log("Archivos Subidos");                          
        
                        let resp = JSON.parse(uploadedURIsRaw);     
                        // console.log("RESPONSE de la respuesta:", cons_id.cons_id);
        
                    },
                    error: function(err){
                        console.error("Error subiendo archivos:", err);
                        // aún así intentamos enviar historial sin archivos
                        //enviarAGeminiYGuardar(mensajes, cons_id);
                    }
                });
        
            }
            //Se RESETEA el file Elem (bandeja de documentos)
            $('#fileElem').val('');

        }
    });

});

$("#btnenviar").on("click", function () {

    mostrarBarra();
    actualizarBarra(5, "Enviando mensaje...");

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
        console.log("Este es el nombre del archivo"+files[i].name);
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
            actualizarBarra(15, "Mensaje guardado");

            mostrar(cons_id); // Recarga chat del usuario
            $('#fileElem').val('');
            $('#prompt').summernote('reset');
            // 2 OBTENER HISTORIAL
            $.post(
                "../../controller/consulta.php?op=obtener_historial",
                { cons_id: cons_id },
                function (historialRaw) {
                    
                    let historial = JSON.parse(historialRaw);
                    let mensajes = historial.map(row => ({
                        role: (row.usu_id == 2 ? "model" : "user"),
                        parts: [{ text: row.det_contenido }]
                    }));

                    actualizarBarra(25, "Historial cargado");


                    $.post(
                        //OBTENEMOS INFORMACION DE LOS OBJETOS DEL BUCKET/CONSULTA: mime-type + gsUtil
                        "../../controller/consulta.php?op=obtener_Info_Gsutil",
                        { cons_id: cons_id },
                        function (contentType_GSutilRaw) {
                            if (contentType_GSutilRaw.length > 0){}
                            actualizarBarra(65, "Preparando documentos para IA");

                            let contentType_GSutil = JSON.parse(contentType_GSutilRaw);
                            
                            let partes = [];
                            //AGREGAR TEXTO ASIGNADO POR EL USUARIO AL PROMPT
                            partes.push({
                                text: `Analiza el/los documentos adjuntos y responde claramente a la siguiente solicitud;\n\n${prompt}`
                            });
                            //SE AGREGAN LOS RECURSOS PARA QUE GEMINI LEA LOS ARCHIVOS
                            contentType_GSutil.forEach(element => {
                                partes.push({
                                    file_data: {
                                        mime_type: element.contentType,
                                        file_uri: element.gs_util
                                    }
                                });
                            });
                            let mensajes = [];
                            //SE ADJUNTA TODO EL CONTENIDO DEL MENSAJE + ROL USER
                            mensajes.push({
                                role: "user",
                                parts: partes
                            });
                            console.log("Partes de archivos para Gemini:", mensajes);
                            //SE ENVIA TODO EL CONTENIDO A VERTEX/GEMINI + ID DE LA CONSULTA
                            actualizarBarra(75, "Analizando Documentos...");

                            enviarAGeminiYGuardar(mensajes, cons_id);
                        }
                        
                    );
                    // 5 ENVIAR A GEMINI
                    // if (files.length > 0) {
                    //     let uploadData = new FormData();
                    //     uploadData.append("cons_id", cons_id);
                    //     for (let i = 0; i < files.length; i++) uploadData.append("files[]", files[i]);

                    //     $.ajax({
                    //         url: "../../controller/consulta.php?op=subir_archivos_cloud",
                    //         type: "POST",
                    //         data: uploadData,
                    //         processData: false,
                    //         contentType: false,
                    //         success: function (uploadedURIsRaw) {
                    //             actualizarBarra(55, "Archivos procesados");

                    //             let resp = JSON.parse(uploadedURIsRaw);                               
                    //             // console.log("RESPONSE de la respuesta:", cons_id.cons_id);
                                

                    //         },
                    //         error: function(err){
                    //             console.error("Error subiendo archivos:", err);
                    //             // aún así intentamos enviar historial sin archivos
                    //             //enviarAGeminiYGuardar(mensajes, cons_id);
                    //         }
                    //     });

                    // } else {
                    //     // No hay archivos → enviamos historial inmediatamente
                    //     actualizarBarra(75, "Generando respuesta...");
                    //     enviarAGeminiYGuardar(mensajes, cons_id);

                    // }
                }
            );

            $('#btnenviar').prop("disabled", false);
            $('#btnenviar').html('Enviar y Procesar');
            $('#prompt').val('');

        }
    });
    

});

//ESCUCHO EL CLIC DE UN BOTON CREADO DINAMICAMENTE
$(document).on("click", ".btnEliminarDoc", function () {
    const params = new URLSearchParams(window.location.search);
    const cons_id = params.get("ID");
    let docd_id = $(this).data("docid");

    let uploadData = new FormData();
    uploadData.append('cons_id', cons_id);
    uploadData.append('docd_id', docd_id);

    swal(
        {
            title: "¿Eliminar Documento?",
            text: "Se eliminará este documento de la consulta",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-warning",
            confirmButtonText: "Si",    
            cancelButtonText: "No",
            closeOnConfirm: false,
        },
        function(isConfirm) {
            if (isConfirm) {
                swal.close();
                console.log("Doc ID:", docd_id);

                $.ajax({
                    url: "../../controller/consulta.php?op=eliminar_archivo_bucket",
                    type: "POST",
                    data: uploadData,
                    processData: false,
                    contentType: false,
                    success: function (uploadedURIsRaw) {
                        //actualizarBarra(55, "Archivos procesados");
        
                        //let resp = JSON.parse(uploadedURIsRaw);                               
                        // console.log("RESPONSE de la respuesta:", cons_id.cons_id);
        
                        $.ajax({
                            url:"../../controller/documento.php?op=delete_documento",
                            type: "POST",
                            data: {docd_id: docd_id},
                            success: function(datos){
                                console.log(datos);
        
                                refrescar_detalle(cons_id);
                                
                                $.unblockUI();
        
                            },
                        });
                    },
                    error: function(err){
                        console.error("Error subiendo archivos:", err);
                        // aún así intentamos enviar historial sin archivos
                        //enviarAGeminiYGuardar(mensajes, cons_id);
                    }
                });

                
            }
        }
    );


});

//Funcion que envia a Gemini y guarda la respuesta
function enviarAGeminiYGuardar(mensajes, cons_id) {
    
    $.post("../../controller/consulta.php?op=ai_prompt",
        { mensajes: JSON.stringify(mensajes) },
        function (response) {
            //console.log("Gemini respondió unas cosas:", response);

            actualizarBarra(100, "Finalizado");
            try {
                var json = JSON.parse(response);

                let respuestaIA = "⚠ Gemini no devolvió contenido textual.";
                console.log("Respuesta Gemini completa:", json);

                if (json.candidates?.[0]?.content?.parts?.[0]?.text) {
                    respuestaIA = json.candidates[0].content.parts[0].text;
                    console.log("Respuesta textual extraída de Gemini:", respuestaIA);
                }  

                // if (
                //     json.candidates &&
                //     json.candidates.length > 0 &&
                //     json.candidates[0].content &&
                //     json.candidates[0].content.parts &&
                //     json.candidates[0].content.parts.length > 0
                // ) {
                //     const textoPart = json.candidates[0].content.parts.find(p => p.text);
                //     if (textoPart) {
                //         respuestaIA = textoPart.text;
                //     }
                // }

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
        //console.log(data.cons_nom);
        data = JSON.parse(data); 

        $('#lblnomconsulta').html("Consulta: " + data.cons_nom);
    });
}

function refrescar_detalle(id) {

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
        
    });

    $.post("../../controller/consulta.php?op=mostrar", {cons_id: id}, function (data) {
        //console.log(data.cons_nom);
        data = JSON.parse(data); //line

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