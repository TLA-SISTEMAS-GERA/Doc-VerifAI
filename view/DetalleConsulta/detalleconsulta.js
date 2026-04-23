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
    let cons_id = params.get("ID");

    
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


//CARGAR DOCUMENTO/S

// $("#btncargar").on("click", async function () {

//     const cons_id = new URLSearchParams(window.location.search).get("ID");
//     let files = $("#fileElem")[0].files;

//     if (files.length === 0) {
//         swal({
//             title: "Bandeja vacía",
//             text: "No has cargado documento/s",
//             type: "warning"
//         });
//         return;
//     }

//     blockPnl('Preparando carga...');

//     // 🔥 1. PEDIR URLS FIRMADAS
//     let formData = new FormData();
//     formData.append("cons_id", cons_id);

//     for (let i = 0; i < files.length; i++) {
//         formData.append("files[]", files[i]);
//     }
//     console.log("Pidiendo URLs firmadas...");

//     let response = await $.ajax({
//         url: "../../controller/consulta.php?op=generar_urls",
//         type: "POST",
//         data: formData,
//         processData: false,
//         contentType: false
//     });
//     console.log("Respuesta recibida:", response);

//     let urls = JSON.parse(response);

//     blockPnl('Subiendo archivos...');

//     // 🔥 2. SUBIR DIRECTO A CLOUD
//     await Promise.all(urls.map(async (item, i) => {

//         let res = await fetch(item.url, {
//             method: "PUT",
//             body: files[i]
//         });
    
//         console.log(`Archivo ${i}:`, res.status);
    
//         if (!res.ok) {
//             let err = await res.text();
//             console.error(err);
//             throw new Error("Error subiendo archivo");
//         }
    
//     }));

//     //3. REGISTRAR EN BD
//     blockPnl('Registrando documentos...');

//     let registroData = new FormData();
//     registroData.append("cons_id", cons_id);
//     registroData.append("files", JSON.stringify(urls));

//     $.ajax({
//         url: "../../controller/consulta.php?op=insertdetalle",
//         type: "POST",
//         data: registroData,
//         processData: false,
//         contentType: false,
//         success: function () {
//             blockPnl('Carga finalizada');
//             mostrar(cons_id);
//             $("#fileElem").val('');
//             unblockPnl();
//         }
//     });
// });

//VERSION PRINCIPAL
$("#btncargar").on("click", function() { 
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

    //SE RECORRE FILES DEL FORMDATA PARA SUBIRLOS UNO X UNO
    if (files.length > 0) {
        blockPnl('Cargando documento/s a cloudStorage...');
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
                let resp = JSON.parse(uploadedURIsRaw); 
                blockPnl('Registrando documento/s...');

                $.ajax({
                    //INSERTO UN DETALLE DE CARGA DE ARCHIVOS SOLAMENTE
                    url: "../../controller/consulta.php?op=insertdetalle",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        
                        //Se muestra el detalle
                        blockPnl('Carga finalizada.');
                        mostrar(cons_id);

                        $("#fileElem").val('');
                        $('#btnenviar').removeAttr('disabled').addClass('btn btn-rounded btn-inline btn_primary');
                        unblockPnl();
                    }
                });
                
                console.log("Archivos Subidos");                          
            },
            error: function(err){
                console.error("Error subiendo archivos:", err);
                blockPnl("Error al subir archivo/s");
                setTimeout(unblockPnl, 2000);
                // aún así intentamos enviar historial sin archivos
                //enviarAGeminiYGuardar(mensajes, cons_id);
            }
        });
        ocultarBarra();

        
    }else {
        unblockPnl();
        swal({
            title: "Bandeja vacía",
            text: "No has cargado documento/s",
            type: "warning",
            confirmButtonClass: "btn-warning"
        });
    }
    //Se RESETEA el file Elem (bandeja de documentos)

});


// $("#btncargar").on("click", async function () {

//     const params = new URLSearchParams(window.location.search);
//     const cons_id = params.get("ID");

//     var usu_id = $('#user_idx').val();
//     var prompt = $('#prompt').val();

//     let files = $("#fileElem")[0].files;

//     if (files.length === 0) {
//         swal({
//             title: "Bandeja vacía",
//             text: "No has cargado documento/s",
//             type: "warning",
//             confirmButtonClass: "btn-warning"
//         });
//         return;
//     }

//     blockPnl('Subiendo documentos en lotes...');

//     try {
//         // 🔥 1. SUBIR ARCHIVOS EN LOTES
//         await subirEnLotes(files, cons_id);

//         // 🔥 2. REGISTRAR EN BD (UNA SOLA VEZ)
//         blockPnl('Registrando documento/s...');

//         var formData = new FormData();
//         formData.append('cons_id', cons_id);
//         formData.append('usu_id', usu_id);
//         formData.append('det_contenido', prompt);

//         // ⚠️ Opcional: si tu insertdetalle necesita archivos, los vuelves a mandar
//         for (let i = 0; i < files.length; i++) {
//             formData.append("files[]", files[i]);
//         }

//         $.ajax({
//             url: "../../controller/consulta.php?op=insertdetalle",
//             type: "POST",
//             data: formData,
//             contentType: false,
//             processData: false,
//             success: function () {
//                 blockPnl('Carga finalizada.');
//                 mostrar(cons_id);

//                 $("#fileElem").val('');
//                 unblockPnl();
//             }
//         });

//     } catch (err) {
//         console.error("Error en la carga:", err);
//         blockPnl("Error al subir archivos");
//         setTimeout(unblockPnl, 2000);
//     }

// });

//ENVIAR PROMPT/ GENERAR RESPUESTA

$("#btnenviar").on("click", function () {

    blockPnl('Cargando información...');
    mostrarBarra();
    actualizarBarra(5, "Procesando información...");

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
            blockPnl('Espera un momento...');
            mostrar(cons_id); // Recarga chat del usuario
            $('#fileElem').val('');
            $('#prompt').summernote('reset');
            // 2 OBTENER HISTORIAL
            actualizarBarra(20, "Cargando informacion");
            $.post(
                "../../controller/consulta.php?op=obtener_historial",
                { cons_id: cons_id },
                function (historialRaw) {
                    
                    let historial = JSON.parse(historialRaw);
                    let mensajes = historial.map(row => ({
                        role: (row.usu_id == 2 ? "model" : "user"),
                        parts: [{ text: row.det_contenido }]
                    }));

                    actualizarBarra(25, "Cargando informacion");
                    console.log("Historial cargado");
                    blockPnl('Recopilando información...')

                    $.post(
                        //OBTENEMOS INFORMACION DE LOS OBJETOS DEL BUCKET/CONSULTA: mime-type + gsUtil
                        "../../controller/consulta.php?op=obtener_Info_Gsutil",
                        { cons_id: cons_id },
                        function (contentType_GSutilRaw) {
                            if (contentType_GSutilRaw.length > 0){}
                            blockPnl('Obteniendo datos de los documentos adjuntos...')
                            actualizarBarra(45, "Preparando documentos para IA");

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
                            blockPnl('Carga de información a Gemini AI...')
                            actualizarBarra(55, "Documentos en procesamiento");

                            enviarAGeminiYGuardar(mensajes, cons_id);
                        }   
                    );
                }
            );

            $('#btnenviar').prop("disabled", false);
            $('#btnenviar').html('✨ Enviar y Procesar');
            $('#prompt').val('');
        }
    });
});

//SUBIDA DE ARCHIVOS POR LOTES
function dividirArchivos(files, tamañoChunk = 10) {
    let chunks = [];
    for (let i = 0; i < files.length; i += tamañoChunk) {
        chunks.push(files.slice(i, i + tamañoChunk));
    }
    return chunks;
}

// async function subirEnLotes(files, cons_id) {
//     const chunks = dividirArchivos(Array.from(files), 10);

//     for (let i = 0; i < chunks.length; i++) {
//         let formData = new FormData();
//         formData.append("cons_id", cons_id);

//         chunks[i].forEach(file => {
//             formData.append("files[]", file);
//         });

//         await $.ajax({
//             url: "../../controller/consulta.php?op=subir_archivos_cloud",
//             type: "POST",
//             data: formData,
//             processData: false,
//             contentType: false
//         });

//         console.log(`Lote ${i + 1} subido`);
//     }
// }

async function subirEnLotes(files, cons_id) {

    const chunks = dividirArchivos(Array.from(files), 10);

    const CONCURRENCIA = 3; //cuantos lotes al mismo tiempo

    for (let i = 0; i < chunks.length; i += CONCURRENCIA) {

        let grupo = chunks.slice(i, i + CONCURRENCIA);

        // 🔥 ejecuta varios a la vez
        await Promise.all(
            grupo.map((chunk, index) => {

                let formData = new FormData();
                formData.append("cons_id", cons_id);

                chunk.forEach(file => {
                    formData.append("files[]", file);
                });

                return $.ajax({
                    url: "../../controller/consulta.php?op=subir_archivos_cloud",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false
                }).then(() => {
                    console.log(`Lote ${i + index + 1} subido`);
                });

            })
        );
    }
}

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

    blockPnl('En espera de respuesta por parte del Agente...')
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
            <article class="activity-line-item box-typical mensaje ia" id="msg_${det_id}">
                <div class="activity-line-date"></div>

                <div class="activity-line-action-list">
                    <section class="activity-line-action">
                        
                        <div class="time">Generando Respuesta...</div>

                        <div class="cont">
                            <div class="cont-in">
                                <p class="texto"></p>
                            </div>
                        </div>

                    </section>
                </div>
            </article>
        `);
        
        const respuestaIA = $(`#msg_${det_id} .texto`);

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
        blockPnl('Generando respuesta...')
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
                        headers: {
                            "Content-Type":"application/x-www-form-urlencoded"
                        },
                        body:new URLSearchParams({
                            det_id:det_id,
                            det_contenido:respuestaCompleta
                        })
                    });
                    unblockPnl()
                    mostrar(cons_id);
                    return;
                }

                try {
                    //MOSTRANDO LA RESPUESTA POR PARTES AL HTML CREADO (respuestaIA)
                    const data = JSON.parse(json);
                    const textoChunk = data.candidates?.[0]?.content?.parts?.[0]?.text;

                    if (!textoChunk) continue; 
                    respuestaCompleta += textoChunk;
                    /* Mostrar en pantalla */
                    // respuestaIA.html(respuestaCompleta); 
                    const html = marked.parse(respuestaCompleta);
                    const cleanHtml = DOMPurify.sanitize(html);

                    respuestaIA.html(cleanHtml);
                    scrollToBottom();
                } catch(e) {
                    console.warn("Chunk inválido:", json);
                }
            }
        }
    } catch (error) {
        console.error("Error:", error);
    }
}

//FUNCION PARA BLOQUEAR EL PANEL DE DETALLE (mensaje de carga)
function blockPnl(mensaje) {
    
    $('#pnldetalle').block({
        message: `<div class="blockui-default-message">` +
                    `<i class="fa fa-circle-o-notch fa-spin"></i>` +
                    `<h6>`+mensaje+`</h6>` +
                 `</div>`,
        overlayCSS: {
            background: 'rgba(24, 44, 68, 0.8)', //dark
            opacity: 1,
            cursor: 'wait'
        },
        css: {
            width: '50%'
        },
        blockMsgClass: 'block-msg-default'
    });
}

function unblockPnl() {
    $('#pnldetalle').unblock();

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
                $('#lblnomconsulta').html("<div class='form-error-text-block'>❌ Ocurrió un error al cargar datos de la consulta.</div>"); //AQUI ES DONDE  A VECES FALLA
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
            $('#lbldetalle').html("<div class='form-error-text-block'>❌ Ocurrió un error al cargar el detalle.</div>");
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