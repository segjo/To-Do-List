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
            } else if (this.status == 424) {
                alert("Ihr Konto wurde noch nicht aktiviert.");
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

function mainView_UploadAvatar(evt) {
    var uploadFile = evt.target.files[0];
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                mainView_RefreshProfileInfo();
            } else if (this.status == 401) {
                loginScreen_ShowPage();
            }
        }
    };

    loadingMessageShow(true);
    var formData = new FormData();
    formData.append("image", uploadFile);
    xhr.open("POST", "/api/profile/uploadAvatar", true);
    xhr.send(formData);
}

function mainView_RefreshProfileInfo() {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            loadingMessageShow(false);
            if (this.status == 200) {
                var response = JSON.parse(xhr.responseText);
                updateAvatarFileUrlInCookies(response.userAvatar);
                mainView_UpdateUserPicture(response.userAvatar);
                document.getElementById("txt_user_first_name").value = response.userFirstName;
                document.getElementById("txt_user_last_name").value = response.userFirstName;
                loadingMessageShow(false);
            } else if (this.status == 401) {
                loginScreen_ShowPage();
            }
        }
    };

    xhr.open("GET", "/api/profile/info", true);
    xhr.send(null);
}

function loginScreen_ShowPage() {
    window.location = "login.html";
}

function registerScreen_RegisterUser() {
    var firstName = document.getElementById("firstName").value;
    var lastName = document.getElementById("lastName").value;
    var email = document.getElementById("email").value;
    var userName = document.getElementById("userName").value;
    var password = document.getElementById("password").value;

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 201) {
                loginScreen_ShowPage()
            } else if (this.status == 409) {
                alert("Benutzername bereits registriert.");
            }
        }
    };

    var formData = new FormData();
    formData.append("firstName", firstName);
    formData.append("lastName", lastName);
    formData.append("email", email);
    formData.append("userName", userName);
    formData.append("password", password);

    xhr.open("POST", "/api/profile/create", true);
    xhr.send(formData);
}

function mainView_FillWithUserData() {
    try {
        var user = JSON.parse(Cookies.get("user"));
        document.getElementById("logged_user").innerHTML = user.userName;
        mainView_UpdateUserPicture(user.userAvatar);
    } catch (error) {
        loginScreen_ShowPage();
    }
}

function mainView_UpdateUserPicture(url) {
    document.getElementById("user_picture").src = url;
    document.getElementById("user_picture_manage_profile").src = url;
}

function mainView_FillTodoListList(selectFirstList, selectListId) {
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
                    listListItems += mainView_GetTodoListListItem(lists[i].ListId, lists[i].Name);
                }

                listListItems += '</div>';

                document.getElementById("lists").innerHTML = listListItems;

                if (selectFirstList && lists.length > 0) {
                    mainView_FillTodoListEntries(lists[0].ListId, lists[0].Name);
                } else if (selectListId) {
                    for (i = 0; i < lists.length; i++) {
                        if (lists[i].ListId == selectListId) {
                            mainView_FillTodoListEntries(lists[i].ListId, lists[i].Name);
                            break;
                        }
                    }
                }

            } else if (this.status == 401) {
                loginScreen_ShowPage();
            }
        }
    };

    xhr.open("GET", "/api/profile/lists", true);
    xhr.send(null);
}

function mainView_FillTodoListEntries(listId, todoListTitle) {
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

                var todoListEntryContainerElement = document.getElementById("list_entries");
                todoListEntryContainerElement.innerHTML = listItems;

                var todoListTitleElement = document.getElementById("todo_list_title");
                todoListTitleElement.innerHTML = todoListTitle;

                mainView_AddEventListenerForAddNewListEntryInputBox(listId);

                document.getElementById("current_list_id").innerText = listId;

            } else if (this.status == 401) {
                loginScreen_ShowPage();
            }
        }
    };

    xhr.open("GET", "/api/todolist/" + listId + "/items", true);
    xhr.send(null);
}

function mainView_DeleteCurrentList() {
    var listId = document.getElementById("current_list_id").innerText;

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                mainView_FillTodoListList(true);
            } else if (this.status == 401) {
                loginScreen_ShowPage();
            }
        }
    };

    var formData = new FormData();
    formData.append("listId", listId);
    xhr.open("POST", "/api/todolist/" + listId + "/delete", true);
    xhr.send(formData);
}

function mainView_UpdateDoneState(element, listId, entryId) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 401) {
                loginScreen_ShowPage();
            } else {

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
                var deadlineElement = document.getElementById("entry_deadline_" + entryId);
                deadlineElement.innerText = deadline;
                deadlineElement.classList.add("font-weight-bold");
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

function mainView_CreateListFromEditorDialog() {
    var listName = document.getElementById("txt_create_list").value;

    mainView_CreateList(listName);
}

function mainView_CreateList(listName) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 201) {
                var response = JSON.parse(this.responseText);
                mainView_FillTodoListList(false, response.ListId);
            } else if (this.status == 401) {
                loginScreen_ShowPage();
            }
        }
    };

    var formData = new FormData();
    formData.append("listName", listName);

    xhr.open("POST", "/api/todolist/create", true);
    xhr.send(formData);
}

function mainView_UpdateTodoListEntryDescriptionTextBoxEvent(event, entryId) {
    if (event.keyCode == 13) {
        event.target.display = "none";
    }
}

function mainView_GetTodoListListItem(listId, title) {

    var listItem = '';

    listItem += '<div id="listId_' + listId + '" class="list-group-item todo_list" onclick="javascript:mainView_FillTodoListEntries(' + listId + ',\'' + title + '\');">';
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
    var deadline_element_class = deadline ? "font-weight-bold" : "text-muted";
    var priority = "";

    deadline = deadline || "keine Frist festgelegt";

    listItem += '<div id="todoListEntryId_' + entryId + '" class="list-group-item">';
    listItem += '<a href="#" class="list-group-item list-group-item-action flex-column align-items-start">';
    listItem += '<div class="d-flex w-100 justify-content-between">';
    listItem += '  <h5 class="mb-1">';
    listItem += '     <div class="checkbox float-left"><label style="font-size: 1em"><input type="checkbox" id="' + checkboxId + '" ' + ((state == 1) ? "checked" : "") + '>';
    listItem += '       <span onclick="javascript:mainView_UpdateDoneState(' + checkboxId + ', ' + listId + ', ' + entryId + ');" class="cr"><i class="cr-icon fa fa-check"></i></span></label>';
    listItem += '     </div>';
    listItem += '     <span id="entry_description_' + entryId + '" onclick="javascript:mainView_ShowListEntryItemEditor(this, ' + listId + ', ' + entryId + ');">' + itemDescription + '</span>';
    listItem += '  </h5>';
    listItem += '  <small><img class="icon_small float-right" src="img/icon_delete2.png" onclick="mainView_ShowListEntryDeleteConfirmDialog(' + listId + ', ' + entryId + ');"><img class="icon_small float-right" src="img/icon_priority.png" data-toggle="modal" data-target="#modal_set_priority"><img class="icon_small float-right" src="img/icon_calendar.png" onclick="mainView_ShowListEntryDeadlineEditor(' + listId + ', ' + entryId + ');"></small>';
    listItem += '</div>';
    listItem += '<p class="mb-1"></span></p>';
    listItem += '<small><span class="">' + priority + '</span><span class="' + deadline_element_class + '" id="entry_deadline_' + entryId + '">' + deadline + '</span></small>';
    listItem += '</a>';
    listItem += '</div>';

    return listItem;
}

function mainView_ShowListEntryDeleteConfirmDialog(listId, entryId) {
    document.getElementById("set_delete_entry_current_list_id").innerHTML = listId;
    document.getElementById("set_delete_entry_current_entry_id").innerHTML = entryId;

    $('#modal_confirm_delete_entry').modal();
}

function mainView_DeleteEntryFromConfirmDialog() {
    listId = document.getElementById("set_delete_entry_current_list_id").innerHTML;
    entryId = document.getElementById("set_delete_entry_current_entry_id").innerHTML;

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                var todoListTitleElement = document.getElementById("todo_list_title");
                mainView_FillTodoListEntries(listId, todoListTitleElement.innerHTML);
            } else if (this.status == 401) {
                loginScreen_ShowPage();
            } else if (this.status == 422) {
                $('#modal_invalid_entry').modal();
            }
        }
    };

    var formData = new FormData();
    formData.append("itemId", entryId);

    xhr.open("POST", "/api/todolist/" + listId + "/items/delete", true);
    xhr.send(formData);
}

function mainView_ShowListEntryDeadlineEditor(listId, entryId) {
    document.getElementById("set_deadline_current_list_id").innerHTML = listId;
    document.getElementById("set_deadline_current_entry_id").innerHTML = entryId;
    document.getElementById("set_deadline_current_entry_deadline").value = document.getElementById("entry_deadline_" + entryId).innerText;

    $('#modal_set_deadline').modal();
}

function mainView_ShareListFromEditorDialog() {
    var listId = document.getElementById("current_list_id").innerHTML;
    var userName = document.getElementById("txt_share_list_to").innerHTML;
    var permission = document.getElementById("share_list_permission").innerHTML;

    mainView_ShareList(listId, userName, permission);
}

function mainView_ShareList(listId, userName, permission) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 201) {

            } else if (this.status == 401) {

            } else if (this.status == 422) {

            }
        }
    };

    var formData = new FormData();
    formData
    formData.append("userName", userName);

    xhr.open("POST", "/api/todolist/" + listId + "/share", true);
    xhr.send(formData);
}

function mainView_ShowListEntryItemEditor(element, listId, entryId) {

}

function mainView_addTodoListItem(listId, itemDescription) {

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 201) {
                var todoListTitleElement = document.getElementById("todo_list_title");
                mainView_FillTodoListEntries(listId, todoListTitleElement.innerHTML);
            } else if (this.status == 401) {
                loginScreen_ShowPage();
            } else if (this.status == 422) {
                $('#modal_invalid_entry').modal();
            }
        }
    };

    var formData = new FormData();
    formData.append("itemName", itemDescription);

    xhr.open("POST", "/api/todolist/" + listId + "/items/add", true);
    xhr.send(formData);
}

function mainView_AddEventListenerForAddNewListEntryInputBox(listId) {
    document.querySelector(".todo_list_entry_add").addEventListener("keyup", function(event) {
        if (event.key !== "Enter") {
            return;
        }
        mainView_addTodoListItem(listId, event.srcElement.value);
        event.srcElement.value = "";
    });
}

function updateAvatarFileUrlInCookies(newFileUrl) {
    var user = JSON.parse(Cookies.get("user"));
    user.userAvatar = newFileUrl;
    Cookies.set('user', JSON.stringify(user));
}

function loadingMessageShow(show) {
    document.getElementById('loading_message').style.display = show ? 'block' : 'none';
    document.getElementById('loading_over').style.display = show ? 'block' : 'none';
}