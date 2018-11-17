function loginScreen_ApiLogin() {
    var userName = document.getElementById("userName").value;
    var password = document.getElementById("password").value;

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                window.location = "main.html";
                var user = JSON.parse(xhr.responseText).Content;
                Cookies.set('user', JSON.stringify(user));
                Cookies.set('PHPSESSID', user.userSessionId);
            } else {
                $('#modal_login_failed').modal();
            }
        }
    };

    var formData = new FormData();
    formData.append("userName", userName);
    formData.append("password", password);

    xhr.open("POST", "/api/profile/login", true);
    xhr.send(formData);
}

function mainView_FillWithUserData() {
    var user = JSON.parse(Cookies.get("user"));
    document.getElementById("logged_user").innerHTML = user.userName;
    document.getElementById("user_picture").src = ("https://todo.mynas.ch/" + user.userAvatar);
}

function mainView_FillTodoListList(todoListContainerId, todoListEntryContainerId, todoListTitleId) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                console.log(xhr.responseText);
                var lists = JSON.parse(xhr.responseText).Content;

                var listListItems = '<div class="list-group" id="todo_list_list">';
                listListItems += '<div class="list-group-item">';
                listListItems += '<button class="btn todo_list_create_new_list w-100" id="todo_list_create_new_list" data-toggle="modal" data-target="#modal_create_list">Liste erstellen</button>';
                listListItems += '</div>';

                listListItems += '<div>';

                for (var i = 0; i < 2; i++) {
                    listListItems += getTodoListListItem(todoListContainerId, todoListEntryContainerId, todoListTitleId, i, "List " + i);
                }

                listListItems += '</div>';

                document.getElementById(todoListContainerId).innerHTML = listListItems;
            }
        }
    };

    xhr.open("POST", "/api/profile/lists", true);
    xhr.send(null);
}









function addTodoListItem(listId, itemDescription) {
    document.getElementById("todo_list_entry_list").innerHTML += getTodoListEntryItem(listId, itemDescription);
    addEventListenerForAddNewListEntryInputBox(listId);
}

function fillTodoListList(todoListContainerId, todoListEntryContainerId, todoListTitleId) {

    var listListItems = '<div class="list-group" id="todo_list_list">';
    listListItems += '<div class="list-group-item">';
    listListItems += '<button class="btn todo_list_create_new_list w-100" id="todo_list_create_new_list" data-toggle="modal" data-target="#modal_create_list">Liste erstellen</button>';
    listListItems += '</div>';

    listListItems += '<div>';

    for (var i = 0; i < 2; i++) {
        listListItems += getTodoListListItem(todoListContainerId, todoListEntryContainerId, todoListTitleId, i, "List " + i); // TODO: Get data from API
    }

    listListItems += '</div>';

    document.getElementById(todoListContainerId).innerHTML = listListItems;
}

function fillTodoListEntries(listId, todoListEntryContainerId, todoListTitleId) {
    var listItems = '';
    var i = 0;

    listItems += '<div class="list-group" id="todo_list_entry_list">';
    listItems += '<div class="list-group-item"><input class="form-control todo_list_entry_add" type="text"/></div>';

    for (i = 0; i < 3; i++) {
        listItems += getTodoListEntryItem(listId);
    }

    var todoLists = document.getElementsByClassName("todo_list");

    for (i = 0; i < todoLists.length; i++) {
        todoLists[i].classList.remove("active");
    }

    var selectedTodoList = document.getElementById("listId_" + listId);
    selectedTodoList.classList.add("active");

    listItems += "</div>";

    var todoListEntryContainer = document.getElementById(todoListEntryContainerId);
    todoListEntryContainer.innerHTML = listItems;

    var todoListTitle = document.getElementById(todoListTitleId);
    todoListTitle.innerHTML = "TITLE";

    addEventListenerForAddNewListEntryInputBox(listId);
}

function addEventListenerForAddNewListEntryInputBox(listId) {
    document.querySelector(".todo_list_entry_add").addEventListener("keyup", function(event) {
        if (event.key !== "Enter") {
            return;
        }
        addTodoListItem(listId, event.srcElement.value);
        event.srcElement.value = "";
    });
}

function getTodoListListItem(todoListContainerId, todoListEntryContainerId, todoListTitleId, listId, title) {

    var listItem = '';

    listItem += '<div id="listId_' + listId + '" class="list-group-item todo_list" onclick="javascript:fillTodoListEntries(' + listId + ',\'' + todoListEntryContainerId + '\', \'' + todoListTitleId + '\', \'' + todoListContainerId + '\');">';
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

function getTodoListEntryItem(listId, itemDescription) {
    var listItem = '';
    var title = itemDescription || ("Entry " + (Math.floor(Math.random() * 1000)));
    listItem += '<div id="listId_' + listId + '" class="list-group-item">';
    listItem += '<a href="#" class="list-group-item list-group-item-action flex-column align-items-start">';
    listItem += '<div class="d-flex w-100 justify-content-between">';
    listItem += '  <h5 class="mb-1" onclick="javascript:showListEntryItemEditor("listId_' + listId + '");">' + title + '</h5>';
    listItem += '  <small><img class="icon_small float-right" src="img/icon_priority.png" data-toggle="modal" data-target="#modal_set_priority"><img class="icon_small float-right" src="img/icon_calendar.png" data-toggle="modal" data-target="#modal_set_deadline"></small>';
    listItem += '</div>';
    listItem += '<p class="mb-1"></p>';
    listItem += '<small>blah</small>';
    listItem += '</a>';
    listItem += '</div>';

    return listItem;
}

function createList(todoListContainerId, todoListEntryContainerId, todoListTitleId) {
    var listName = document.getElementById("txt_create_list").value;
    document.getElementById("todo_list_list").innerHTML += getTodoListListItem(todoListContainerId, todoListEntryContainerId, todoListTitleId, 89, listName);
}