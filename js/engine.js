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

function loginScreen_ShowPage() {
    window.location = "login.html";
}

function mainView_FillWithUserData() {
    try {
        var user = JSON.parse(Cookies.get("user"));
        document.getElementById("logged_user").innerHTML = user.userName;
        document.getElementById("user_picture").src = user.userAvatar;
    } catch (error) {
        loginScreen_ShowPage();
    }
}

function mainView_FillTodoListList(todoListContainerId, todoListEntryContainerId, todoListTitleId) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {

                var lists = JSON.parse(xhr.responseText).lists;

                var listListItems = '<div class="list-group" id="todo_list_list">';
                listListItems += '<div class="list-group-item">';
                listListItems += '<button class="btn todo_list_create_new_list w-100" id="todo_list_create_new_list" data-toggle="modal" data-target="#modal_create_list">Liste erstellen</button>';
                listListItems += '</div>';

                listListItems += '<div>';

                for (var i = 0; i < lists.length; i++) {
                    listListItems += mainView_GetTodoListListItem(todoListContainerId, todoListEntryContainerId, todoListTitleId, lists[i].ListId, lists[i].Name);
                }

                listListItems += '</div>';

                document.getElementById(todoListContainerId).innerHTML = listListItems;
            } else if (this.status == 401) {
                loginScreen_ShowPage();
            }
        }
    };

    xhr.open("GET", "/api/profile/lists", true);
    xhr.send(null);
}

function mainView_FillTodoListEntries(listId, todoListEntryContainerId, todoListTitleId, todoListContainerId, todoListTitle) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {

                var entries = JSON.parse(xhr.responseText).entries;
                var listItems = '';
                var i = 0;

                listItems += '<div class="list-group" id="todo_list_entry_list">';
                listItems += '<div class="list-group-item"><input class="form-control todo_list_entry_add" type="text"/></div>';

                for (i = 0; i < entries.length; i++) {
                    listItems += mainView_GetTodoListEntryItem(listId, entries[i].ItemId, entries[i].Name, entries[i].Deadline, entries[i].State);
                }

                var todoLists = document.getElementsByClassName("todo_list");

                for (i = 0; i < todoLists.length; i++) {
                    todoLists[i].classList.remove("active");
                }

                var selectedTodoList = document.getElementById("listId_" + listId);
                selectedTodoList.classList.add("active");

                listItems += "</div>";

                var todoListEntryContainerElement = document.getElementById(todoListEntryContainerId);
                todoListEntryContainerElement.innerHTML = listItems;

                var todoListTitleElement = document.getElementById(todoListTitleId);
                todoListTitleElement.innerHTML = todoListTitle;

                addEventListenerForAddNewListEntryInputBox(listId);

            } else if (this.status == 401) {
                loginScreen_ShowPage();
            }
        }
    };

    xhr.open("GET", "/api/todolist/" + listId + "/items", true);
    xhr.send(null);
}


function mainView_UpdateDoneState(element, listId, entryId) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 401) {
                loginScreen_ShowPage();
            } else {
                element.checked = !element.checked;
            }
        }
    };

    var formData = new FormData();
    formData.append("state", (element.checked ? "0" : "1"));
    formData.append("sortIndex", "");
    formData.append("deadline", "");
    formData.append("itemName", document.getElementById("entry_description_" + entryId).innerText);

    xhr.open("POST", "/api/todolist/" + listId + "/items/" + entryId, true);
    xhr.send(formData);
}

function mainView_UpdateDeadlineFromEditorDialog() {
    var deadline = document.getElementById("set_deadline_current_entry_deadline").value + " 00:00:00";
    var listId = document.getElementById("set_deadline_current_list_id").innerHTML;
    var entryId = document.getElementById("set_deadline_current_entry_id").innerHTML;

    mainView_UpdateDeadline(deadline, listId, entryId);
}

function mainView_UpdateDeadline(deadline, listId, entryId) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                document.getElementById("entry_deadline_" + entryId).innerText = deadline;
            } else if (this.status == 401) {
                loginScreen_ShowPage();
            }
        }
    };

    var formData = new FormData();
    formData.append("state", "");
    formData.append("sortIndex", "");
    formData.append("deadline", deadline);
    formData.append("itemName", document.getElementById("entry_description_" + entryId).innerText);

    xhr.open("POST", "/api/todolist/" + listId + "/items/" + entryId, true);
    xhr.send(formData);
}

function mainView_UpdateTodoListEntryDescriptionTextBoxEvent(event, entryId) {
    if (event.keyCode == 13) {
        event.target.display = "none";
    }
}

function mainView_GetTodoListListItem(todoListContainerId, todoListEntryContainerId, todoListTitleId, listId, title) {

    var listItem = '';

    listItem += '<div id="listId_' + listId + '" class="list-group-item todo_list" onclick="javascript:mainView_FillTodoListEntries(' + listId + ',\'' + todoListEntryContainerId + '\', \'' + todoListTitleId + '\', \'' + todoListContainerId + '\', \'' + title + '\');">';
    listItem += '<a href="#" class="list-group-item list-group-item-action flex-column align-items-start">';
    listItem += '<div class="d-flex w-100 justify-content-between">';
    listItem += '  <h5 class="mb-1">' + title + '</h5>';
    listItem += '  <small></small>';
    listItem += '</div>';
    listItem += '<p class="mb-1"></p>';
    listItem += '<small></small>';
    listItem += '</a>';
    listItem += '</div>';

    return listItem;
}

function mainView_GetTodoListEntryItem(listId, entryId, itemDescription, deadline, state) {
    var checkboxId = 'checkbox_done_entryId_' + entryId;
    var listItem = '';

    deadline = deadline || "";

    listItem += '<div id="todoListEntryId_' + entryId + '" class="list-group-item">';
    listItem += '<a href="#" class="list-group-item list-group-item-action flex-column align-items-start">';
    listItem += '<div class="d-flex w-100 justify-content-between">';
    listItem += '  <h5 class="mb-1" onclick="javascript:mainView_ShowListEntryItemEditor(this, ' + listId + ', ' + entryId + ');">';
    listItem += '     <div class="checkbox float-left"><label style="font-size: 1em"><input type="checkbox" id="' + checkboxId + '" ' + ((state == 1) ? "checked" : "") + '>';
    listItem += '       <span onclick="javascript:mainView_UpdateDoneState(' + checkboxId + ', ' + listId + ', ' + entryId + ');" class="cr"><i class="cr-icon fa fa-check"></i></span></label>';
    listItem += '     </div>';
    listItem += '     <input type="text" id="todo_list_entry_description_editor_entryId_' + entryId + '" class="todo_list_entry_description_editor" onkeydown="mainView_UpdateTodoListEntryDescriptionTextBoxEvent(event, ' + entryId + ');">';
    listItem += '     <span id="entry_description_' + entryId + '">' + itemDescription + '</span>';
    listItem += '  </h5>';
    listItem += '  <small><img class="icon_small float-right" src="img/icon_priority.png" data-toggle="modal" data-target="#modal_set_priority"><img class="icon_small float-right" src="img/icon_calendar.png" onclick="mainView_ShowListEntryDeadlineEditor(' + listId + ', ' + entryId + ');"></small>';
    listItem += '</div>';
    listItem += '<p class="mb-1"></span></p>';
    listItem += '<small><span id="entry_deadline_' + entryId + '">' + deadline + '</span></small>';
    listItem += '</a>';
    listItem += '</div>';

    return listItem;
}

function mainView_ShowListEntryDeadlineEditor(listId, entryId) {
    document.getElementById("set_deadline_current_list_id").innerHTML = listId;
    document.getElementById("set_deadline_current_entry_id").innerHTML = entryId;
    document.getElementById("set_deadline_current_entry_deadline").value = document.getElementById("entry_deadline_" + entryId).innerText;

    $('#modal_set_deadline').modal();
}

function mainView_ShowListEntryItemEditor(element, listId, entryId) {
    var editorElement = document.getElementById("todo_list_entry_description_editor_entryId_" + entryId);
    editorElement.display = "block";

    console.log(element);
}











function addTodoListItem(listId, itemDescription) {
    document.getElementById("todo_list_entry_list").innerHTML += getTodoListEntryItem(listId, itemDescription);
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

function createList(todoListContainerId, todoListEntryContainerId, todoListTitleId) {
    var listName = document.getElementById("txt_create_list").value;
    document.getElementById("todo_list_list").innerHTML += getTodoListListItem(todoListContainerId, todoListEntryContainerId, todoListTitleId, 89, listName);
}