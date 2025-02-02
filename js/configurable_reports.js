M.block_configurable_reports = {

    sesskey: null,

    init: function(Y, sesskey) {
        this.Y = Y;
        this.sesskey = sesskey;
    },

    loadReportCategories: function(Y, sesskey) {
        this.Y = Y;
        this.sesskey = sesskey;

        select_reportcategories = Y.one('#id_crreportcategories');
        Y.io(M.cfg.wwwroot+'/blocks/configurable_reports/repository.php', {
            data: 'action=listreports&sesskey=' + sesskey,
            context: this,
            method: "GET",
            on: {
                success: function(id, o) {
                    var response = Y.JSON.parse(o.responseText);

                    for(var prop in response) {
                        if (response.hasOwnProperty(prop)) {
                            option = Y.Node.create('<option value='+response[prop]["path"]+'>'+response[prop]["name"]+'</option>');
                            select_reportcategories.appendChild(option);
                        }
                    }

                },
                failure: function(id, o) {
                    // TODO use strings.
                    window.alert('Repository unreachable');
                }
            }
        });

    },

    onchange_crreportcategories : function (select_element,sesskey) {
        var Y = this.Y;

        select_reportnames = Y.one('#id_crreportnames');

        var xhr = Y.io(M.cfg.wwwroot+'/blocks/configurable_reports/repository.php', {
            data: 'action=listcategory&category='+select_element[select_element.selectedIndex].value+'&sesskey='+sesskey,
            context: this,
            method: "GET",
            on: {
                success: function(id, o) {
                    var response = Y.JSON.parse(o.responseText);
                    select_reportnames.get('childNodes').remove();
                    option = Y.Node.create('<option value="-1">...</option>');
                    select_reportnames.appendChild(option);

                    for(var prop in response) {
                        if (response.hasOwnProperty(prop)) {
                            option = Y.Node.create('<option value='+ encodeURIComponent(response[prop]["path"]) +'>'+response[prop]["name"]+'</option>');
                            select_reportnames.appendChild(option);
                        }
                    }
                },
                failure: function(id, o) {
                    window.alert('Repository unreachable');
                }
            }
        });
    },

    onchange_crreportnames : function (select_element,sesskey) {
        var Y = this.Y;


        var path = select_element[select_element.selectedIndex].value;
        location.href = location.href + "&importpath=" + encodeURIComponent(path);
    },

    onchange_reportcategories : function (select_element,sesskey) {
        var Y = this.Y;

        //select_reportsincategory = Y.one('#id_reportsincategory');
        //select_reportsincategory.setHTML('');

        select_reportsincategory = Y.one('#id_reportsincategory');
        select_reportsincategory.setStyle('visibility', 'hidden');
        var xhr = Y.io(M.cfg.wwwroot+'/blocks/configurable_reports/list_reports_in_category.php', {
            data: 'category='+select_element[select_element.selectedIndex].value+'&sesskey='+sesskey,
            context: this,
            method: "GET",
            on: {
                success: function(id, o) {
                    var response = Y.JSON.parse(o.responseText);
                    select_reportsincategory.get('childNodes').remove();
                    option = Y.Node.create('<option value="-1">Choose...</option>');
                    select_reportsincategory.appendChild(option);

                    for(var prop in response) {
                        if (response.hasOwnProperty(prop)) {
                            option = Y.Node.create('<option value='+response[prop]["fullname"]+'>'+response[prop]["name"]+'</option>');
                            select_reportsincategory.appendChild(option);
                        }
                    }
                    select_reportsincategory.setStyle('visibility', 'visible');
                },
                failure: function(id, o) {
                    if (o.statusText != 'abort') {
                        select_reportsincategory.setStyle('visibility', 'hidden');
//                        var instance = this.currentinstance;
//                        instance.progress.setStyle('visibility', 'hidden');
//                        if (o.statusText !== undefined) {
//                            instance.listcontainer.set('innerHTML', o.statusText);
//                        }
                    }
                }
            }
        });
    },

    onchange_reportsincategory : function (select_element,sesskey) {
        var Y = this.Y;

        //select_reportsincategory = Y.one('#id_reportsincategory');
        //select_reportsincategory.setHTML('');

        textarea_reportsincategory = Y.one('#id_remotequerysql');
        //select_reportsincategory.setStyle('visibility', 'hidden');
        var xhr = Y.io(M.cfg.wwwroot+'/blocks/configurable_reports/get_remote_report.php', {
            data: 'reportname='+select_element[select_element.selectedIndex].value+'&sesskey='+sesskey,
            context: this,
            method: "GET",
            on: {
                success: function(id, o) {
                    var response = Y.JSON.parse(o.responseText);

                    // Use regular textarea element.
                    textarea_reportsincategory.set('value', response);

                    // Use codemirror editor.
                    var remoteQueryTextarea = document.getElementById('id_remotequerysql');
                    var remoteQueryEditor = remoteQueryTextarea.parentElement.querySelector('.CodeMirror').CodeMirror;

                    if (remoteQueryEditor !== undefined) {
                        remoteQueryEditor.setValue(response);
                    }

                    /*
                    var list = Y.Node.create('<select>');
                    for(var prop in response) {
                        if (response.hasOwnProperty(prop)) {
                            option = Y.Node.create('<option id='+response[prop]["name"]+'>'+response[prop]["name"]+'</option>');
                            list.appendChild(option);
                        }
                    }
                    //select_reportsincategory.setStyle('visibility', 'visible');
                    list.setAttribute('id','id_reportsincategory');
                    list.setAttribute('name','reportsincategory');
                    list.setAttribute('onchange','M.block_configurable_reports.onchange_reportsincategory(this,"'+this.sesskey+'")');
                    select_reportsincategory.replace(list);
                    */
                },
                failure: function(id, o) {
                    if (o.statusText != 'abort') {
                        select_reportsincategory.setStyle('visibility', 'hidden');
//                        var instance = this.currentinstance;
//                        instance.progress.setStyle('visibility', 'hidden');
//                        if (o.statusText !== undefined) {
//                            instance.listcontainer.set('innerHTML', o.statusText);
//                        }
                    }
                }
            }
        });
    },

    onchange_menuplugin: function(selectElement) {
        const id = selectElement.dataset.id;
        const comp = selectElement.dataset.comp;

        location.href = `editplugin.php?id=${id}&comp=${comp}&pname=${selectElement.value}`;
    }
}
