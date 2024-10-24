$(function () {
  /// modal create asset model
  // This code will run when the modal is about to be shown
  $("#modal-create-model").on("show.bs.modal", function () {
    var modal = $(this);

    // Use modal.find instead of $(this).find
    var name = modal.find(".modal-body #name");
    var category_id = modal.find(".modal-body #category_id");
    var manufacturer_id = modal.find(".modal-body #manufacturer_id");
    var model_number = modal.find(".modal-body #modal-model_number");
    var fieldset_id = modal.find(".modal-body #modal-fieldset_id");
    var url = $("#modal-save-model").data("model");

    // Unbind previous click event handlers
    modal.find("#modal-save-model").off("click");

    // Attach click event handler to #modal-save button inside the modal
    modal.find("#modal-save-model").on("click", function () {
      if (
        name.val() === "" ||
        category_id.val() === "" ||
        manufacturer_id.val() === "" ||
        model_number.val() === "" ||
        fieldset_id.val() === ""
      ) {
        alert("Fill all form");
      } else {
        $.ajax({
          type: "POST",
          url: url,
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
          },
          data: {
            name: name.val(),
            category_id: category_id.val(),
            manufacturer_id: manufacturer_id.val(),
            model_number: model_number.val(),
            fieldset_id: fieldset_id.val(),
          },
          success: function (t) {
            if ("error" == t.status) {
              var i = "";
              for (var r in t.messages)
                i +=
                  "<li>Problem(s) with field <i><strong>" +
                  r +
                  "</strong></i>: " +
                  t.messages[r];
              return $("#modal_error_msg").html(i).show(), !1;
            }
            var o = t.payload.id,
              s =
                t.payload.name ||
                t.payload.first_name + " " + t.payload.last_name;
            if (!o || !s)
              return (
                console.error(
                  "Could not find resulting name or ID from modal-create. Name: " +
                    s +
                    ", id: " +
                    o
                ),
                !1
              );
            modal.find('.modal-body input[type="text"]').val("");

            // Clear the select elements
            name.val("");
            category_id.val("");
            manufacturer_id.val("");
            model_number.val("");
            fieldset_id.val("");
            // If using Select2, trigger the change event to update the styling
            category_id.trigger("change");
            manufacturer_id.trigger("change");
            fieldset_id.trigger("change");
            modal.modal("hide");

            var modelSelect = $("#model_select_id");
            modelSelect.append(
              '<option value="' + o + '" selected="selected">' + s + "</option>"
            );
            modelSelect.trigger("change"); // If using Select2, trigger the change event
            // Optionally, you can also hide the error message
            $("#modal_error_msg").hide();
            var a = $("#" + n);
            var e = "model_select_id";
            a.length > 0 && a.bootstrapTable("refresh");
            var l = document.getElementById(e);
            if (!l) return !1;
            (l.options[l.length] = new Option(s, o)),
              (l.selectedIndex = l.length - 1),
              $(l).trigger("change"),
              window.fetchCustomFields && fetchCustomFields();
          },
          error: function (t) {
            (msg = t.responseJSON.messages || t.responseJSON.error),
              $("#modal_error_msg")
                .html("Server Error: " + msg)
                .show();
          },
        });
      }
    });
  });

  ///

  $("#modal-create-category").on("show.bs.modal", function () {
    var modal = $(this);
    // Use modal.find instead of $(this).find
    var name = modal.find(".modal-body #name");
    var category = modal.find(".modal-body #category_type");
    var url = $("#save-category").data("category");
    // Unbind previous click event handlers
    modal.find("#save-category").off("click");

    modal.find("#save-category").on("click", function () {
      if (name.val() === "" || category.val() === "") {
        alert("Fill name category");
      } else {
        $.ajax({
          type: "POST",
          url: url,
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
          },
          data: {
            name: name.val(),
            category_type: category.val(),
          },
          success: function (t) {
            if ("error" == t.status) {
              var i = "";
              for (var r in t.messages)
                i +=
                  "<li>Problem(s) with field <i><strong>" +
                  r +
                  "</strong></i>: " +
                  t.messages[r];
              return $("#modal_error_msg").html(i).show(), !1;
            }
            var o = t.payload.id,
              s =
                t.payload.name ||
                t.payload.first_name + " " + t.payload.last_name;
            if (!o || !s)
              return (
                console.error(
                  "Could not find resulting name or ID from modal-create. Name: " +
                    s +
                    ", id: " +
                    o
                ),
                !1
              );
            name.val("");
            category.val("");
            modal.modal("hide");

            var modelSelect = $("#category_id");
            modelSelect.append(
              '<option value="' + o + '" selected="selected">' + s + "</option>"
            );
            modelSelect.trigger("change"); // If using Select2, trigger the change event
            // Optionally, you can also hide the error message
            $("#modal_error_msg").hide();
          },
          error: function (t) {
            (msg = t.responseJSON.messages || t.responseJSON.error),
              $("#modal_error_msg")
                .html("Server Error: " + msg)
                .show();
          },
        });
      }
    });
  });

  /// modal manufacturer

  $("#modal-create-manufacturer").on("show.bs.modal", function () {
    var modal = $(this);
    // Use modal.find instead of $(this).find
    var name = modal.find(".modal-body #name");
    var url = $("#save-manufacturer").data("manufacturer");
    // Unbind previous click event handlers
    modal.find("#save-manufacturer").off("click");
    modal.find("#save-manufacturer").on("click", function () {
      if (name.val() === "") {
        alert("Fill name manufacturer");
      } else {
        $.ajax({
          type: "POST",
          url: url,
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
          },
          data: {
            name: name.val(),
          },
          success: function (t) {
            if ("error" == t.status) {
              var i = "";
              for (var r in t.messages)
                i +=
                  "<li>Problem(s) with field <i><strong>" +
                  r +
                  "</strong></i>: " +
                  t.messages[r];
              return $("#modal_error_msg").html(i).show(), !1;
            }
            var o = t.payload.id,
              s =
                t.payload.name ||
                t.payload.first_name + " " + t.payload.last_name;
            if (!o || !s)
              return (
                console.error(
                  "Could not find resulting name or ID from modal-create. Name: " +
                    s +
                    ", id: " +
                    o
                ),
                !1
              );
            name.val("");
            modal.modal("hide");
            var modelSelect = $("#manufacturer_id");
            modelSelect.append(
              '<option value="' + o + '" selected="selected">' + s + "</option>"
            );
            modelSelect.trigger("change"); // If using Select2, trigger the change event
            // Optionally, you can also hide the error message
            $("#modal_error_msg").hide();
          },
          error: function (t) {
            (msg = t.responseJSON.messages || t.responseJSON.error),
              $("#modal_error_msg")
                .html("Server Error: " + msg)
                .show();
          },
        });
      }
    });
  });

  // modal asset suppplier
  $("#modal-create-asset-supplier").on("show.bs.modal", function () {
    let modal = $(this);
    // Use modal.find instead of $(this).find
    let name = modal.find(".modal-body #name");
    let url = $("#save-asset-supplier").data("company");
    // Unbind previous click event handlers
    modal.find("#save-asset-supplier").off("click");
    modal.find("#save-asset-supplier").on("click", function () {
      if (name.val() === "") {
        alert("Fill name asset supplier");
      } else {
        $.ajax({
          type: "POST",
          url: url,
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
          },
          data: {
            name: name.val(),
          },
          success: function (t) {
            if ("error" == t.status) {
              var i = "";
              for (var r in t.messages)
                i +=
                  "<li>Problem(s) with field <i><strong>" +
                  r +
                  "</strong></i>: " +
                  t.messages[r];
              return $("#modal_error_msg").html(i).show(), !1;
            }
            var o = t.payload.id,
              s =
                t.payload.name ||
                t.payload.first_name + " " + t.payload.last_name;
            if (!o || !s)
              return (
                console.error(
                  "Could not find resulting name or ID from modal-create. Name: " +
                    s +
                    ", id: " +
                    o
                ),
                !1
              );
            name.val("");
            modal.modal("hide");
            var modelSelect = $("#company_id");
            modelSelect.append(
              '<option value="' + o + '" selected="selected">' + s + "</option>"
            );
            modelSelect.trigger("change"); // If using Select2, trigger the change event
            // Optionally, you can also hide the error message
            $("#modal_error_msg").hide();
          },
          error: function (t) {
            (msg = t.responseJSON.messages || t.responseJSON.error),
              $("#modal_error_msg")
                .html("Server Error: " + msg)
                .show();
          },
        });
      }
    });
  });
});
