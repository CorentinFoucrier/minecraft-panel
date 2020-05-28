const token = $("#token").val();
const saveRoles = $("#save_roles");
const topIndex = 0; // index of top row
const spin = $("#spin");
let roleWarning = false;
let g_username; // used for #editUserRole modal
let g_role; // used for #editRolePermissions modal

let usersTable = $("#users_table").DataTable({
    language: { url: baseUri + "assets/js/dataTableSettings.json" },
    columnDefs: [
        { targets: "_all", orderable: false },
        { targets: [3, 4], width: "25%", searchable: false },
        { targets: 0, searchable: false, visible: false },
    ],
    info: false,
    pagingType: "simple_numbers",
});

// https://datatables.net/plug-ins/sorting/absolute#Example
let reorderType = $.fn.dataTable.absoluteOrderNumber([
    { value: topIndex, position: "top" },
]);

let rolesTable = $("#roles_table").DataTable({
    language: { url: baseUri + "assets/js/dataTableSettings.json" },
    rowReorder: { selector: ".allow-reorder" },
    columnDefs: [
        { targets: "_all", orderable: false },
        { targets: 0, type: reorderType, visible: false },
        { targets: [2, 3], width: "22%", searchable: false },
        {
            targets: 1,
            className: "allow-reorder reorder",
            createdCell: (td, cellData, rowData, row, col) => {
                if (cellData == "owner") {
                    $(td).removeClass("allow-reorder reorder");
                }
            },
        },
    ],
    info: false,
    pagingType: "simple_numbers",
});

// https://datatables.net/reference/event/row-reorder
rolesTable.on("row-reordered", (e, diff, edit) => {
    let topRow = false; // Is top row involved in reorder?
    for (let i = 0; i < diff.length; i++) {
        const d = diff[i];
        if (d.oldData == topIndex) {
            topRow = true;
            break;
        }
    }
    if (topRow) {
        // Back to previous state
        rolesTable.one("draw", () => {
            for (let i = 0; i < diff.length; i++) {
                let row = rolesTable.row(diff[i].node); // Get the Datatable row
                let data = row.data(); // Get the row data
                data[0] = diff[i].oldData; // Update the index column with the oldData (old index)
                row.data(data); // Apply the updated data
            }
            // Sort the table after the updates
            rolesTable.draw();
        });
    }
    if (saveRoles.attr("disabled")) {
        saveRoles.removeAttr("disabled");
    }
});

$(document).ready(function () {
    $(".permission").parent().css("opacity", 0); // hide permissions checkboxes until AJAX values comes
});

// Modal events listener
$("#editRolePermissions").on({
    "shown.bs.modal": (e) => {
        // Trigger when modal is shown
        $.post(
            "/settings/get_role_permission",
            {
                token: token,
                role: g_role,
            },
            (data) => {
                if (data) {
                    switch (data.state) {
                        case "success":
                            for (let i = 0; i < data.permissions.length; i++) {
                                const perm_id = data.permissions[i]; // id of permission in database
                                $(`#perm_${perm_id}`).prop("checked", true);
                            }
                            $(".permission").parent().css("opacity", 1);
                            spin.addClass("d-none");
                            break;
                        case "warning":
                            if (roleWarning == false) {
                                toastr.warning(
                                    data.warning.message,
                                    data.warning.title
                                );
                            } else {
                                roleWarning = false;
                            }
                            $(".permission").parent().css("opacity", 1);
                            spin.addClass("d-none");
                            break;
                        case "error":
                            toastr.error(data.error.message, data.error.title);
                            break;
                    }
                }
            },
            "json"
        );
    },
    "hidden.bs.modal": (e) => {
        // Trigger when modal is hidden
        $("input[type=checkbox].permission").prop("checked", false);
        $(".permission").parent().css("opacity", 0);
        spin.removeClass("d-none");
    },
});

$("input[type=checkbox].permission").on("click", (e) => {
    let $this = $(e.currentTarget); // Due to ES6 Arrow Function
    let checked = $this.prop("checked"); // bool
    let permission = $this.attr("name"); // name html attribute

    $.post(
        "/settings/edit_role_permission",
        {
            token: token,
            checked: checked,
            permission: permission,
            role: g_role,
        },
        (data) => {
            if (data) {
                switch (data.state) {
                    case "error":
                        toastr.error(data.error.message, data.error.title);
                        if (checked == true) {
                            $this.prop("checked", false);
                        } else {
                            $this.prop("checked", true);
                        }
                        break;
                }
            }
        },
        "json"
    );
});

const deleteRow = (id) => {
    $("#" + id).fadeOut(500, () => {
        usersTable
            .row("#" + id)
            .remove()
            .draw();
        $("#" + id).remove();
    });
};

const addNewUser = () => {
    let username = $("#addNewUser_username");
    let role = $("#addNewUser_role option:selected").html();
    let rank = $("#addNewUser_role");
    $.post(
        "/settings/add_new_user",
        {
            username: username.val(),
            role: role,
            token: token,
        },
        (data) => {
            if (data) {
                switch (data.state) {
                    case "success":
                        toastr.success(
                            data.success.message,
                            data.success.title
                        );
                        // Add a new row to the 'usersTable' then redraw and store the newly row into 'rowNode'
                        let rowNode = usersTable.row
                            .add([
                                rank.val(),
                                username.val(),
                                role,
                                `<button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#editUserRole" onclick="g_username = $('#${username.val()}')">
                                <i style="pointer-events: none;" class="far fa-edit"></i>
                            </button>`,
                                `<button class="btn btn-danger btn-sm float-right" onclick="deleteUser('${username.val()}');">
                                <i style="pointer-events: none;" class="far fa-trash-alt"></i>
                            </button>`,
                            ])
                            .draw()
                            .node();
                        // Target the new row then add an id to it set with username.
                        $(rowNode).attr("id", username.val());
                        $("#passwordModal").modal("show"); // show up the modal
                        $("#target_password").val(data.generatedPassword); // Display generated password into modal input
                        username.val("");
                        rank.val("default");
                        break;
                    case "invalid":
                        toastr.error(data.invalid.message, data.invalid.title);
                        break;
                    case "error":
                        toastr.error(data.error.message, data.error.title);
                        break;
                }
            } else {
                toastr.error("Error");
            }
        },
        "json"
    );
};

const deleteUser = (username) => {
    $.post(
        "/settings/delete_user",
        {
            username: username,
            token: token,
        },
        (data) => {
            if (data) {
                switch (data.state) {
                    case "deleted":
                        toastr.success(
                            data.deleted.message,
                            data.deleted.title
                        );
                        deleteRow(username);
                        break;
                    case "notExist":
                        toastr.error(
                            data.notExist.message,
                            data.notExist.title
                        );
                        deleteRow(username);
                        break;
                    case "error":
                        toastr.error(data.error.message, data.error.title);
                        break;
                }
            }
        },
        "json"
    );
};

const editUserRole = () => {
    let rank = $("#editUserRole_role");
    let role = $("#editUserRole_role option:selected").html();
    $.post(
        "/settings/edit_user_role",
        {
            username: g_username.attr("id"),
            role: role,
            token: token,
        },
        (data) => {
            if (data) {
                switch (data.state) {
                    case "success":
                        let row = usersTable.row(g_username);
                        let rowData = row.data();
                        rowData[0] = rank.val();
                        rowData[2] = role;
                        row.data(rowData).draw();
                        $("#editUserRole").modal("hide");
                        rank.val("default");
                        toastr.success(
                            data.success.message,
                            data.success.title
                        );
                        break;
                    case "error":
                        toastr.error(data.error.message, data.error.title);
                        break;
                }
            } else {
                toastr.error("Error");
            }
        },
        "json"
    );
};

const saveRolesOrder = () => {
    // Make a copy of original rolesTable for exclude html button of final payload.
    const rows = Array.from(rolesTable.data());
    let rolesData = [];
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        rolesData[row[0]] = row[1];
    }
    $.post(
        "/settings/save_roles_order",
        {
            token: token,
            data: JSON.stringify(rolesData),
        },
        (data) => {
            if (data) {
                switch (data.state) {
                    case "success":
                        save_roles.attr("disabled", "disabled");
                        toastr.success(
                            data.success.message,
                            data.success.title
                        );
                        break;

                    case "error":
                        toastr.error(data.error.message, data.error.title);
                        break;
                }
            }
        },
        "json"
    );
};

const addNewRole = () => {
    let role = $("#addNewRole_role");
    $.post(
        "/settings/add_new_role",
        {
            token: token,
            role: role.val(),
        },
        (data) => {
            if (data) {
                switch (data.state) {
                    case "success":
                        // Add a new row to the 'rolesTable' then redraw and store the newly row into 'rowNode'
                        let rowNode = rolesTable.row
                            .add([
                                data.rank,
                                role.val(),
                                `<button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#editRolePermissions" onclick="g_role = '${role.val()}'">
                                <i style="pointer-events: none;" class="far fa-edit"></i>
                            </button>`,
                                `<button class="btn btn-danger btn-sm float-right" onclick="deleteRole('${role.val()}');">
                                <i style="pointer-events: none;" class="far fa-trash-alt"></i>
                            </button>`,
                            ])
                            .draw()
                            .node();
                        // Target the new row then add an id to it set with username.
                        $(rowNode).attr("id", role.val());
                        g_role = role.val();
                        roleWarning = true;
                        $("#editRolePermissions").modal("show");
                        $("#addNewUser_role, #editUserRole_role").append(
                            `<option value="${
                                data.rank
                            }">${role.val()}</option>`
                        );
                        role.val("");
                        toastr.success(
                            data.success.message,
                            data.success.title
                        );
                        break;
                }
            }
        },
        "json"
    );
};

const deleteRole = (role) => {
    $.post(
        "/settings/delete_role",
        {
            token: token,
            role: role,
        },
        (data) => {
            if (data) {
                switch (data.state) {
                    case "success":
                        $(`
                        #addNewUser_role option:contains("${role}"),
                        #editUserRole_role option:contains("${role}")`).remove();
                        deleteRow(role);
                        toastr.success(
                            data.success.message,
                            data.success.title
                        );
                        break;

                    case "error":
                        toastr.error(data.error.message, data.error.title);
                        break;
                }
            }
        },
        "json"
    );
};
