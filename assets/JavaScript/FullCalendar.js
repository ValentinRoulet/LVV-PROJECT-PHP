var title = '';
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {

        initialView: 'dayGridMonth',
        initialDate: '2021-05-07',
        locale: 'fr',
        //détermine si le calendrier peut être modifier(Ajout, suppression, modification des dates)
        editable: true,


        //position des boutons dans le bandeau du calendrier
        headerToolbar: {
            left: "prev,next",
            center: 'title',
            right: 'addEventButton'
        },

        //ajout du bouton ajout
        customButtons: {
            addEventButton: {
                text: 'Ajouter date',
                click: function() {
                    title = prompt('Entrer le titre');
                    var dateStr = prompt('Entrer la date sous forme ANNEE/MOIS/JOURS');
                    var date = new Date(dateStr + 'T00:00:00'); // will be in local time

                    if (!isNaN(date.valueOf())) { // valid?
                        calendar.addEvent({
                        title: title,
                        start: date,
                        allDay: true
                        });
                        
                        start = moment(date).format('YYYY/MM/DD');
                        $.ajax({
                            url:"../../index.php/index/c_index/insert",
                            type:"POST",
                            data:{title:title, start:start},
                            success:function()
                            {
                                calendar.refetchEvents();
                                alert("Added Successfully");
                                
                            }
                        })
                        
                    } else {
                        alert('Invalid date.');
                    }
                }
            }
        },

        //chargement des dates en appelant la fonction load dans le controlleur
        events:"../../index.php/index/c_index/load",

        //evenement à faire lorsqu'on redimentionne
        eventResize: function(event)
            {
                
                var start = event.event.start;
                start = moment(start).format('YYYY/MM/DD');
                var end = event.event.end;
                end = moment(end).format('YYYY/MM/DD');
                
                var title = event.event.title;
                
                var id = event.event.id;
                
                $.ajax({
                    url:"../../index.php/index/c_index/update",
                    type:"POST",
                    data:{title:title, start:start, end:end, id:id},
                    success:function()
                    {
                        calendar.refetchEvents();
                        alert("Event Update");
                    }
                })
            },

        //evenement à faire lorsqu'on déplace une date
        eventDrop:function(event)
            {
                var start = event.event.start;
                start = moment(start).format('YYYY/MM/DD');
                var end = event.event.end;
                end = moment(end).format('YYYY/MM/DD');
                var title = event.event.title;
                var id = event.event.id;
                $.ajax({
                    url:"../../index.php/index/c_index/update",
                    type:"POST",
                    data:{title:title, start:start, end:end, id:id},
                    success:function()
                    {
                        calendar.refetchEvents();
                        alert("Event Updated");
                    }
                })
        },

        //évenement à faire lorsqu'on clique sur une date déjà existante
        eventClick:function(event)
            {
                if(confirm("Are you sure you want to remove it?"))
                {
                    var id = event.event.id;
                    $.ajax({
                        url:"../../index.php/index/c_index/delete",
                        type:"POST",
                        data:{id:id},
                        success:function()
                        {
                            calendar.refetchEvents();
                            alert('Event Removed');
                        }
                    })
                }
        }
    });

    //affiche le calendrier
    calendar.render();

    
});
