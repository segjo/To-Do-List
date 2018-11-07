function fillTodoListList(todoListContainerId, todoListEntryContainerId) {

    var listListItems = '<div class="list-group">';

    for (var i = 0; i < 10; i++) {
        listListItems += getTodoListListItem(todoListContainerId, todoListEntryContainerId, i, "List " + i); // TODO: Get data from API
    }

    listListItems += '</div>';

    var element = document.getElementById(todoListContainerId).innerHTML = listListItems;

}

function fillTodoListEntries(listId, todoListEntryContainerId) {
    var listItems = '';
    var i = 0;

    for (i = 0; i < 10; i++) {
        var title = "Entry " + ((i + 1) * (listId + 1));
        listItems += '<div id="listId_' + listId + '" class="list-group-item">';
        listItems += '<a href="#" class="list-group-item list-group-item-action flex-column align-items-start">';
        listItems += '<div class="d-flex w-100 justify-content-between">';
        listItems += '  <h5 class="mb-1">' + title + '</h5>';
        listItems += '  <small>blabla</small>';
        listItems += '</div>';
        listItems += '<p class="mb-1"></p>';
        listItems += '<small>blah</small>';
        listItems += '</a>';
        listItems += '</div>';
    }

    var todoLists = document.getElementsByClassName("todo_list");

    for (i = 0; i < todoLists.length; i++) {
        todoLists[i].classList.remove("active");
    }

    var selectedTodoList = document.getElementById("listId_" + listId);
    selectedTodoList.classList.add("active");

    document.getElementById(todoListEntryContainerId).innerHTML = listItems;
}

function getTodoListListItem(todoListContainerId, todoListEntryContainerId, listId, title) {

    var listItem = '';

    listItem += '<div id="listId_' + listId + '" class="list-group-item todo_list" onclick="javascript:fillTodoListEntries(' + listId + ',\'' + todoListEntryContainerId + '\', \'' + todoListContainerId + '\');">';
    listItem += '<a href="#" class="list-group-item list-group-item-action flex-column align-items-start">';
    listItem += '<div class="d-flex w-100 justify-content-between">';
    listItem += '  <h5 class="mb-1">' + title + '</h5>';
    listItem += '  <small>blabla</small>';
    listItem += '</div>';
    listItem += '<p class="mb-1"></p>';
    listItem += '<small>blah</small>';
    listItem += '</a>';
    listItem += '</div>';

    return listItem;
}