var title = '';
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {

        initialView: 'dayGridMonth',
        initialDate: '2021-05-07',
        locale: 'fr',
        editable: true,


        
        headerToolbar: {
            left: "prev,next",
            center: 'title',
            right: 'addEventButton'
        },

        customButtons: {
            addEventButton: {
                text: 'add event...',
                click: function() {
                    title = prompt('Entrer le titre');
                    var dateStr = prompt('Enter a date in YYYY-MM-DD format');
                    var date = new Date(dateStr + 'T00:00:00'); // will be in local time

                    if (!isNaN(date.valueOf())) { // valid?
                        calendar.addEvent({
                        title: title,
                        start: date,
                        allDay: true
                        });
                        
                        start = moment(date).format('YYYY/MM/DD');
                        $.ajax({
                            url:"../../index/c_index/insert",
                            type:"POST",
                            data:{title:title, start:start},
                            success:function()
                            {
                                alert("Added Successfully");
                                
                            }
                        })
                        
                    } else {
                        alert('Invalid date.');
                    }
                }
            }
        },

        events:"../../index.php/index/c_index/load",

        eventResize: function(event)
            {
                
                var start = event.event.start;
                start = moment(start).format('YYYY/MM/DD');
                var end = event.event.end;
                end = moment(end).format('YYYY/MM/DD');
                
                var title = event.event.title;
                
                var id = event.event.id;
                
                $.ajax({
                    url:"../../index/c_index/update",
                    type:"POST",
                    data:{title:title, start:start, end:end, id:id},
                    success:function()
                    {
                        calendar.refetchEvents();
                        alert("Event Update");
                    }
                })
            },

        eventDrop:function(event)
            {
                var start = event.event.start;
                start = moment(start).format('YYYY/MM/DD');
                var end = event.event.end;
                end = moment(end).format('YYYY/MM/DD');
                var title = event.event.title;
                var id = event.event.id;
                $.ajax({
                    url:"../../index/c_index/update",
                    type:"POST",
                    data:{title:title, start:start, end:end, id:id},
                    success:function()
                    {
                        calendar.refetchEvents();
                        alert("Event Updated");
                    }
                })
        },

        eventClick:function(event)
            {
                if(confirm("Are you sure you want to remove it?"))
                {
                    var id = event.event.id;
                    $.ajax({
                        url:"../../index/c_index/delete",
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

        /*
        events: [
            {
                title: 'oui',
                url: 'http://google.com/',
                start: '2021-05-28'
            }
        ]
        */
    });


    calendar.render();

    
});
