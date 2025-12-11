document.addEventListener("DOMContentLoaded", function () {
    $.ajax({
        method: "GET",
        url:
            window.CRM.root +
            "/api/groups/" +
            window.CRM.currentGroup +
            "/roles",
        dataType: "json",
    }).then(function (data) {
        window.CRM.groupRoles = data ?? [];
        $("#newRoleSelection").select2({
            data: window.CRM.groupRoles.map((groupRole) => {
                return {
                    id: groupRole.OptionId,
                    text: i18next.t(groupRole.OptionName),
                };
            }),
        });
        initDataTable();
        //echo '<option value="' . $role['lst_OptionID'] . '">' . $role['lst_OptionName'] . '</option>';
    });

    $(".personSearch").select2({
        minimumInputLength: 2,
        language: window.CRM.shortLocale,
        ajax: {
            url: function (params) {
                return window.CRM.root + "/api/persons/search/" + params.term;
            },
            dataType: "json",
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                };
            },
            processResults: function (rdata, page) {
                return { results: rdata };
            },
            cache: true,
        },
    });

    $(".personSearch").on("select2:select", function (e) {
        window.CRM.groups.promptSelection(
            {
                Type: window.CRM.groups.selectTypes.Role,
                GroupID: window.CRM.currentGroup,
            },
            function (selection) {
                window.CRM.groups
                    .addPerson(
                        window.CRM.currentGroup,
                        e.params.data.objid,
                        selection.RoleID,
                    )
                    .then(function () {
                        $(".personSearch").val(null).trigger("change");
                        window.CRM.DataTableAPI.ajax.reload(); /* we reload the data no need to add the person inside the dataTable */
                    });
            },
        );
    });

    $("#deleteSelectedRows").on("click", function () {
        var deletedRows = window.CRM.DataTableAPI.rows(".selected")
            .data()
            .toArray();
        bootbox.confirm({
            message:
                i18next.t(
                    "Are you sure you want to remove the selected group members?",
                ) +
                " (" +
                deletedRows.length +
                ") ",
            buttons: {
                confirm: {
                    label: i18next.t("Delete"),
                    className: "btn-danger",
                },
                cancel: {
                    label: i18next.t("No"),
                    className: "btn-default",
                },
            },
            callback: function (result) {
                if (result) {
                    deletedRows.forEach(function (value, index) {
                        window.CRM.groups
                            .removePerson(
                                window.CRM.currentGroup,
                                value.PersonId,
                            )
                            .then(function () {
                                var dataTableAPI = window.CRM.DataTableAPI;
                                dataTableAPI
                                    .row(function (idx, data, node) {
                                        if (data.PersonId == value.PersonId) {
                                            return true;
                                        }
                                    })
                                    .remove();
                                dataTableAPI.rows().invalidate().draw(true);
                            });
                    });
                }
            },
        });
    });

    $("#addSelectedToCart").on("click", function () {
        if (window.CRM.DataTableAPI.rows(".selected").length > 0) {
            var selectedPersons = {
                Persons: window.CRM.DataTableAPI.rows(".selected")
                    .data()
                    .toArray()
                    .map(function (val, i) {
                        return val.PersonId;
                    }),
            };
            window.CRM.cart.addPerson(selectedPersons.Persons);
        }
    });

    //copy membership
    $("#addSelectedToGroup").on("click", function () {
        window.CRM.groups.promptSelection(
            {
                Type:
                    window.CRM.groups.selectTypes.Group |
                    window.CRM.groups.selectTypes.Role,
            },
            function (data) {
                selectedRows = window.CRM.DataTableAPI.rows(".selected")
                    .data()
                    .toArray();
                selectedRows.forEach(function (row) {
                    window.CRM.groups.addPerson(
                        data.GroupID,
                        row.PersonId,
                        data.RoleID,
                    );
                });
            },
        );
    });

    $("#moveSelectedToGroup").on("click", function () {
        window.CRM.groups.promptSelection(
            {
                Type:
                    window.CRM.groups.selectTypes.Group |
                    window.CRM.groups.selectTypes.Role,
            },
            function (data) {
                selectedRows = window.CRM.DataTableAPI.rows(".selected")
                    .data()
                    .toArray();
                selectedRows.forEach(function (value, index) {
                    window.CRM.groups.addPerson(
                        data.GroupID,
                        value.PersonId,
                        data.RoleID,
                    );

                    window.CRM.groups
                        .removePerson(window.CRM.currentGroup, value.PersonId)
                        .then(function () {
                            var dataTableAPI = window.CRM.DataTableAPI;
                            dataTableAPI
                                .row(function (idx, data, node) {
                                    if (data.PersonId == value.PersonId) {
                                        return true;
                                    }
                                })
                                .remove();
                            dataTableAPI.rows().invalidate().draw(true);
                        });
                });
            },
        );
    });

    $("#AddGroupMembersToCart").on("click", function () {
        window.CRM.cart.addGroup($(this).data("groupid"));
    });

    $(document).on("click", ".changeMembership", function (e) {
        var PersonID = $(e.currentTarget).data("personid");
        window.CRM.groups.promptSelection(
            {
                Type: window.CRM.groups.selectTypes.Role,
                GroupID: window.CRM.currentGroup,
            },
            function (selection) {
                window.CRM.groups
                    .addPerson(
                        window.CRM.currentGroup,
                        PersonID,
                        selection.RoleID,
                    )
                    .done(function () {
                        window.CRM.DataTableAPI.row(function (idx, data, node) {
                            if (data.PersonId == PersonID) {
                                data.RoleId = selection.RoleID;
                                return true;
                            }
                        });
                        window.CRM.DataTableAPI.rows().invalidate().draw(true);
                    });
            },
        );
        e.stopPropagation();
    });
});

function initDataTable() {
    var DataTableOpts = {
        ajax: {
            url:
                window.CRM.root +
                "/api/groups/" +
                window.CRM.currentGroup +
                "/members",
            dataSrc: "Person2group2roleP2g2rs",
        },
        columns: [
            // === NAME ===
            {
                width: "auto",
                title: i18next.t("Name"),
                data: "PersonId",
                render: function (data, type, full, meta) {
                    return (
                        '<img src="' +
                        window.CRM.root +
                        "/api/person/" +
                        full.PersonId +
                        '/thumbnail" class="direct-chat-img initials-image" style="width:' +
                        window.CRM.iProfilePictureListSize +
                        "px; height:" +
                        window.CRM.iProfilePictureListSize +
                        'px"> &nbsp <a target="_top" href="PersonView.php?PersonID=' +
                        full.PersonId +
                        '">' +
                        full.Person.FullName +
                        "</a>"
                    );
                },
            },

            // === GROUP ROLE ===
            {
                width: "auto",
                title: i18next.t("Group Role"),
                data: "RoleId",
                render: function (data, type, full, meta) {
                    let thisRole = $(window.CRM.groupRoles).filter(function (index, item) {
                        return item.OptionId == data;
                    })[0];
                    return (
                        i18next.t(thisRole?.OptionName || "") +
                        ' <button class="changeMembership" data-personid=' +
                        full.PersonId +
                        '><i class="fas fa-pen"></i></button>'
                    );
                },
            },

            // === GENDER ===
            {
                width: "auto",
                title: i18next.t("Gender"),
                render: function (data, type, full, meta) {
                    if (full.Person && full.Person.Gender) {
                        switch (full.Person.Gender) {
                            case 0:
                                return i18next.t("Unassigned");
                            case 1:
                                return i18next.t("Male");
                            case 2:
                                return i18next.t("Female");
                            default:
                                return i18next.t("Other");
                        }
                    }
                    return i18next.t("Unassigned");
                },
            },
            
            // === BIRTH DATE ===
            {
                width: "auto",
                title: i18next.t("Birth Date"),
                render: function (data, type, full, meta) {
                    if (
                        full.Person &&
                        full.Person.BirthDay &&
                        full.Person.BirthMonth &&
                        full.Person.BirthYear
                    ) {
                        const day = full.Person.BirthDay.toString().padStart(2, "0");
                        const month = full.Person.BirthMonth.toString().padStart(2, "0");
                        const year = full.Person.BirthYear;
                        return `${day}/${month}/${year}`;
                    }
                    return i18next.t("Unknown");
                },
            },

            // === AGE ===
            {
                width: "auto",
                title: i18next.t("Age"),
                render: function (data, type, full, meta) {
                    if (
                        full.Person &&
                        full.Person.BirthDay &&
                        full.Person.BirthMonth &&
                        full.Person.BirthYear
                    ) {
                        const birthDate = new Date(
                            full.Person.BirthYear,
                            full.Person.BirthMonth - 1, // bulan di JS 0-11
                            full.Person.BirthDay
                        );
                        const ageDifMs = Date.now() - birthDate.getTime();
                        const ageDate = new Date(ageDifMs);
                        return Math.abs(ageDate.getUTCFullYear() - 1970);
                    }
                    return i18next.t("Unknown");
                },
            },

            // === ADDRESS ===
            {
                width: "auto",
                title: i18next.t("Address"),
                data: "Person.Address1",
                render: function (data, type, full, meta) {
                    return data || i18next.t("Unknown");
                },
            },
        ],

        fnDrawCallback: function (oSettings) {
            $("#iTotalMembers").text(oSettings.aoData.length);
        },
        createdRow: function (row, data, index) {
            $(row).addClass("groupRow");
        },
    };

    // === Merge dengan pengaturan DataTable global dari CRM ===
    $.extend(DataTableOpts, window.CRM.plugin.dataTable);
    window.CRM.DataTableAPI = $("#membersTable").DataTable(DataTableOpts);

    // === Event handler untuk checkbox dan selection row tetap sama ===
    $("#isGroupActive").change(function () {
        $.ajax({
            type: "POST",
            url:
                window.CRM.root +
                "/api/groups/" +
                window.CRM.currentGroup +
                "/settings/active/" +
                $(this).prop("checked"),
            dataType: "json",
            encode: true,
        });
    });

    $("#isGroupEmailExport").change(function () {
        $.ajax({
            type: "POST",
            url:
                window.CRM.root +
                "/api/groups/" +
                window.CRM.currentGroup +
                "/settings/email/export/" +
                $(this).prop("checked"),
            dataType: "json",
            encode: true,
        });
    });

    $(document).on("click", ".groupRow", function () {
        $(this).toggleClass("selected");
        var selectedRows = window.CRM.DataTableAPI.rows(".selected").data().length;
        $("#deleteSelectedRows").prop("disabled", !selectedRows);
        $("#deleteSelectedRows").text(
            i18next.t("Remove") +
                " (" +
                selectedRows +
                ") " +
                i18next.t("Members from group")
        );
        $("#buttonDropdown").prop("disabled", !selectedRows);
        $("#addSelectedToGroup").prop("disabled", !selectedRows);
        $("#addSelectedToGroup").html(
            i18next.t("Add") +
                " (" +
                selectedRows +
                ") " +
                i18next.t("Members to another group")
        );
        $("#addSelectedToCart").prop("disabled", !selectedRows);
        $("#addSelectedToCart").html(
            i18next.t("Add") +
                " (" +
                selectedRows +
                ") " +
                i18next.t("Members to cart")
        );
        $("#moveSelectedToGroup").prop("disabled", !selectedRows);
        $("#moveSelectedToGroup").html(
            i18next.t("Move") +
                " (" +
                selectedRows +
                ") " +
                i18next.t("Members to another group")
        );
    });
}
