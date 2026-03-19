function init() {

}

function mostrarBarra() {
    $("#barra_container").show();
    $("#barra_progreso").val(0);
    $("#barra_texto").text("0%");
}

function actualizarBarra(valor, texto = "") {

    $("#barra_progreso").val(valor);

    if (texto) {
        $("#barra_texto").text(texto);
    } else {
        $("#barra_texto").text(valor + "%");
    }
}

function ocultarBarra() {

    $("#barra_progreso").val(100);
    $("#barra_texto").text("Finalizado");

    setTimeout(() => {
        $("#barra_container").hide();
        $("#barra_progreso").val(0);
        $("#barra_texto").text("0%");
    }, 800);
}

$(document).ready(function() {
    const params = new URLSearchParams(window.location.search);
    const cons_id = params.get("ID");
  
    
    $('#prompt').summernote({
        height: 100,
        lang: "es-ES",
        callbacks: {
            onImageUpload: function(image) {
                
                myimagetreat(image[0]);
            },
            onPaste: function (e) {
             
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
       
        formData.append("files[]", files[i]);
        console.log("Archivos agregados al formData");
    }
   

    $.ajax({
        //INSERTO UN DETALLE DE CARGA DE ARCHIVOS SOLAMENTE
        url: "../../controller/consulta.php?op=insertdetalle",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data) {
           
            //Se muestra el detalle
            mostrar(cons_id);

            $('#btnenviar').removeAttr('disabled').addClass('btn btn-rounded btn-inline btn_primary');

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

                            // partes.push({
                            //     text: `${prompt}`
                            // });
                            //SE AGREGAN LOS RECURSOS PARA QUE GEMINI LEA LOS ARCHIVOS
                            contentType_GSutil.forEach(element => {
                                partes.push({
                                    file_data: {
                                        mime_type: element.contentType,
                                        file_uri: element.gs_util
                                    }
                                });
                            });
                            //let mensajes = [];
                            //SE ADJUNTA TODO EL CONTENIDO DEL MENSAJE + ROL USER
                            mensajes.push({
                                role: "user",
                                parts: partes
                            });
                          
                            //SE ENVIA TODO EL CONTENIDO A VERTEX/GEMINI + ID DE LA CONSULTA
                            actualizarBarra(75, "Analizando Documentos...");

                            enviarAGeminiYGuardar(mensajes, cons_id);
                        }
                        
                    );
                
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
               

                $.ajax({
                    url: "../../controller/consulta.php?op=eliminar_archivo_bucket",
                    type: "POST",
                    data: uploadData,
                    processData: false,
                    contentType: false,
                    success: function (uploadedURIsRaw) {
                        //actualizarBarra(55, "Archivos procesados");
        
                        //let resp = JSON.parse(uploadedURIsRaw);                               
                        
        
                        $.ajax({
                            url:"../../controller/documento.php?op=delete_documento",
                            type: "POST",
                            data: {docd_id: docd_id},
                            success: function(datos){
                                
        
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

async function enviarAGeminiYGuardar(mensajes, cons_id){

    actualizarBarra(80, "Generando Respuesta...");

    let respuestaCompleta = "";
    let det_id = null;

    try {

        /*--------------------------------------------------
        1️ Crear el registro vacío para la respuesta IA
        --------------------------------------------------*/

        const detalleVacio = await fetch("../../controller/consulta.php?op=insertdetalle",{
            method:"POST",
            headers:{
                "Content-Type":"application/x-www-form-urlencoded"
            },
            body:new URLSearchParams({
                cons_id:cons_id,
                usu_id:2,
                det_contenido:""
            })
        });
        
        const crearDetalle = await detalleVacio.json();
        
        det_id = crearDetalle.det_id;
        
        /*--------------------------------------------------
        2️ Crear contenedor visual para la respuesta
        --------------------------------------------------*/

        $('#lbldetalle').append(`
            
            <p id="respuestaIA"></p>
			
            
        `);

        const respuestaIA = $('#respuestaIA');

        /*--------------------------------------------------
        3️ Enviar a Gemini
        --------------------------------------------------*/

        const formData = new FormData();
        formData.append('mensajes', JSON.stringify(mensajes));

        const response = await fetch('../../controller/consulta.php?op=ai_prompt', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder('utf-8');

        let ultimoUpdate = Date.now();

        /*--------------------------------------------------
        4️ Leer chunks
        --------------------------------------------------*/

        while (true) {

            const { done, value } = await reader.read();

            if (done) break;

            const chunk = decoder.decode(value, { stream: true });

            const lines = chunk.split("\n");

            for (let line of lines) {

                if (!line.startsWith("data:")) continue;

                const json = line.substring(5).trim();

                if (json === "[DONE]") {

                    console.log("Streaming terminado");

                    await fetch("../../controller/consulta.php?op=updatedetalle",{
                        method:"POST",
                        headers:{
                            "Content-Type":"application/x-www-form-urlencoded"
                        },
                        body:new URLSearchParams({
                            det_id:det_id,
                            det_contenido:respuestaCompleta
                        })
                    });

                    mostrar(cons_id);

                    return;
                }

                try {
                    //MOSTRANDO LA RESPUESTA POR PARTES AL HTML CREADO (respuestaIA)

                    const data = JSON.parse(json);

                    const textoChunk =
                    data.candidates?.[0]?.content?.parts?.[0]?.text;

                    if (!textoChunk) continue;

                    respuestaCompleta += textoChunk;

                    /* Mostrar en pantalla */

                    respuestaIA.html(respuestaCompleta);
                

                } catch(e) {
                    console.warn("Chunk inválido:", json);
                }

            }

        }

    } catch (error) {
        console.error("Error:", error);
    }



}

function mostrar(id) {

    if (!id) {
        $('#lblnomconsulta').html("<div class='form-error-text-block'>❌ ID de consulta inválido.</div>");
        return;
    }

    // Cargar info de la consulta
    $.ajax({
        url: "../../controller/consulta.php?op=mostrar",
        type: "POST",
        data: { cons_id: id },
        success: function (data) {
            try {
                let json = JSON.parse(data);

                $('#lblnomconsulta').html("Consulta: " + json.cons_nom);

                if (json.est == 2) {
                    $('#pnldetalle').hide();
                }

            } catch (err) {
                console.error("Error parseando JSON mostrar():", data);
                $('#lblnomconsulta').html("<div class='form-error-text-block'>❌ Error cargando datos de la consulta.</div>");
            }
        },
        error: function (err) {
            console.error("Error mostrar():", err);
            $('#lblnomconsulta').html("<div class='form-error-text-block'>❌ Error de servidor al cargar la consulta.</div>");
        }
    });

    // Cargar detalle
    $.ajax({
        url: "../../controller/consulta.php?op=listardetalle",
        type: "POST",
        data: { cons_id: id },
        success: function (data) {
            $('#lbldetalle').html(data);

            $('#lbldetalle p').each(function () {
                let raw = $(this).text().trim();
                let html = marked.parse(raw);
                let cleanHtml = DOMPurify.sanitize(html);
                $(this).html(cleanHtml);
            });

            scrollToBottom();
        },
        error: function (err) {
            console.error("Error listardetalle:", err);
            $('#lbldetalle').html("<div class='form-error-text-block'>❌ Error cargando el detalle.</div>");
        }
    });

    
}


function refrescar_detalle(id) {

    $.post("../../controller/consulta.php?op=listardetalle", {cons_id: id}, function (data){
        
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