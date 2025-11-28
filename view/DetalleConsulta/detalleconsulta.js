function init() {

}


$(document).ready(function() {
    const params = new URLSearchParams(window.location.search);
    
    const cons_id = params.get("ID");
    console.log(cons_id);

    mostrar(cons_id);
});

function mostrar(id) {
    $.post("../../controller/consulta.php?op=mostrar", {cons_id: id}, function (data) {
        data = JSON.parse(data);

        console.log(data);

        $('#lblnomconsulta').html("Consulta: " + data.cons_nom);
    });
}