
$(document).ready(function () {
    var tree = JSON.parse($("#tree_node").val());
    $("#treeview").treeview(
        {
            data:tree,
            showCheckbox:true
        });

    $('#treeview').on('nodeChecked', function(event, data) {
        recursionCheck(data.nodeId);
    });

    $('#treeview').on('nodeUnchecked', function(event, data) {
        recursionUnCheck(data.nodeId);
    });
});

function recursionCheck($id) {
    var current = $('#treeview').treeview('getNode',$id);
    for(var key in current.nodes){
        $('#treeview').treeview('checkNode', [ current.nodes[key].nodeId, { silent: true } ]);
        recursionCheck(current.nodes[key].nodeId);
    }
    return null;
}

function recursionUnCheck($id) {
    var current = $('#treeview').treeview('getNode', $id);
    for (var key in current.nodes) {
        $('#treeview').treeview('uncheckNode', [current.nodes[key].nodeId, {silent: true}]);
        recursionUnCheck(current.nodes[key].nodeId);
    }
    return null;
}
