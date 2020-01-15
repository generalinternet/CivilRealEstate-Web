/**
 * @license Copyright © 2018 General Internet
 * @version 0.001
 */
;
(function ($, window, document, undefined) {
    //@todo: touch event handlers
    var hasTouch = 'ontouchstart' in document;
    
    var dayNameArry = [ 'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat' ];
    var eventGUID = 1;
    
    // Returns a boolean about whether the given input is a time string, like "06:40:00" or "06:00"
    function isTimeString(str) {
            return /^\d+\:\d+(?:\:\d+\.?(?:\d{3})?)?$/.test(str);
    }
    
    // Computes HTML classNames for a single-grid element
    function getSingleDayGridClasses(date) {
        var today = moment();
        var classArray = [ 'grid-' + dayNameArry[date.day()] ];

        if (date.isSame(today, 'day')) {
            classArray.push('grid-today');
        }
        else if (date < today) {
            classArray.push('grid-past');
        }
        else {
            classArray.push('grid-future');
        }

        return classArray.join(' ');
    }
    
    var defaults = {
        events:'', // JSON data
        //viewMenus:['month', 'week', 'day'],
        viewMenus:['week'],
        navMenus:['prev', 'next'],
        defaultView:'week',
        dateFormat:'YYYY/MM/DD',
        timeFormat:'k:mm a',
        headerDateFormat:'ddd M/D',
        headerTimeFormat:'h a',
        titleDateFormat:'MMM D',
        titleMonthFormat:'MMMM YYYY',
        datetimeFormat:'YYYY/MM/DD h:mm:ss a',
        gridDateFormat:'D',
        firstDay: 1,//Monday
        color: '#7899b0', 
        minTime: '09:00:00',
        maxTime: '18:00:00',
        views: {
                day: {
                    groupId: 'sourceId',
                    groupData: 'sources',
                    bodyContents: ['title'],
                },
                week: {
                    groupId: 'sourceId',
                    groupData: 'sources',
                    bodyContents: ['start', 'end'],
                },
                month: {
                    bodyContents: ['title'],
                },
            },
    };
    
    /**
    * @param element
    * @param options
    * @constructor
    */
    function GICalendar(instance, options) {
        var _this = this;
            _this.el = instance[0];
            _this.options = options;
        _this.init();
        return _this;
    }
   
    GICalendar.prototype = {
        init:function () {
                $(this.el).addClass('gic');
                $(this.el).append('<div id="gic-top"></div>');
                $(this.el).append('<div id="gic-body"></div>');
                $(this.el).append('<div id="gic-bottom"><div id="gic-current-page" data-page="1"></span></div>');
                this.reset();
                
                //@todo : change the positions of the NavMenu, ViewMenu and title by options
                this.buildNavMenu();
                this.buildTitle();
                this.buildViewMenu();
                
                //Create default view
//                var viewType = this.options.defaultView;
//                this.view = new CalView(this, viewType);
//                $('#gic-view-menus .view-'+ viewType).addClass('active');
                this.createView();
                
                //Create Event manager
                this.eventManager = new EventManager(this);
                
                this.renderView();
        },
        renderView : function (viewType) {
            if (viewType !== undefined && this.view !== undefined) {
                //Changing view
                var currentView = this.view;
                var currentDate = currentView.dateProfile.date;

                this.removeView();
                
                this.createView(viewType, currentDate);
                //var newView = new CalView(this, viewType);
                //newView.setDate(currentDate);
                //this.view = newView;
                //this.eventManager.view = newView;
                //var newDateProfile = newView.dateProfile;
                
                this.eventManager.view = this.view;
                var newDateProfile = this.view.dateProfile;

                var newStart = newDateProfile.start.clone();
                var newEnd = newDateProfile.end.clone();
                if (this.eventManager.isOutOfLoadedRange(newStart, newEnd)) {
                    // buildEvents includes view.render after loading events
                    this.eventManager.buildEventsFromFunction();
                } else {
                    // Render view
                    this.view.render();
                }
                
                // Update active menu
                $('#gic-view-menus .gic-view-menu').removeClass('active');
                $('#gic-view-menus .view-'+ viewType).addClass('active');
            } else {
                if (this.view === undefined) {
                    // Creating view
                    this.createView();
                }
                
                // Rendering view
                this.view.render();
            }
        },
        createView : function (viewType, currentDate) {
            if (viewType === undefined) {
                viewType = this.options.defaultView;
            }
            this.view = new CalView(this, viewType, currentDate);
            $('#gic-view-menus .view-'+ viewType).addClass('active');
        },
        removeView : function () {
            //RemoveVeiw
            $(this.el).find('.gic-view').remove();
        },
        buildViewMenu : function() {
            var viewMenus = this.options.viewMenus;
            var menuLength = viewMenus.length;
            $(this.el).find('#gic-top').addClass('menu-len-'+menuLength);
            $(this.el).find('#gic-top').append('<div id="gic-view-menus"></div>');
            var viewMenuEl = $('#gic-view-menus');
            for (var i=0; i<menuLength; i++) {
                $(viewMenuEl).append('<span class="gic-view-menu view-'+viewMenus[i]+'" data-view-type="'+viewMenus[i]+'">'+viewMenus[i]+'</span>');
            }
            
            //bind event listeners
            var _this = this;
            $('.gic-view-menu').each(function() {
                _this.bindEventListenerToEl(this, 'click', function () {
                    if (!$(this).hasClass('active')) {
                        var viewType = $(this).data('view-type');
                        _this.renderView(viewType);
                    }
                });
            });
        },
        buildNavMenu: function() {
            $(this.el).find('#gic-top').append('<div id="gic-nav-menus"></div>');
            var navMenuEl = $('#gic-nav-menus');
            
            var navMenus = this.options.navMenus;
            for (var i=0; i<navMenus.length; i++) {
                if (navMenus[i] == 'prev') {
                    $(navMenuEl).append('<span class="gic-nav-menu nav-prev">Prev</span>');
                }
                if (navMenus[i] == 'today') {
                    $(navMenuEl).append('<span class="gic-nav-menu nav-today">Today</span>');
                }
                if (navMenus[i] == 'next') {
                    $(navMenuEl).append('<span class="gic-nav-menu nav-next">Next</span>');
                }
            }
            
            //bind event listeners
            var _this = this;
            _this.bindEventListenerToEl($('.nav-prev'), 'click', function () {
                _this.prev();
            });
            _this.bindEventListenerToEl($('.nav-next'), 'click', function () {
                _this.next();
            });
            _this.bindEventListenerToEl($('.nav-today'), 'click', function () {
                _this.today();
            });
        },
        buildTitle: function() {
            $(this.el).find('#gic-top').prepend('<h2 id="gic-title"></h2>');
        },
        prev : function () {
            var view = this.view;
            var currentDateProfile = view.dateProfile;
            var newDate = currentDateProfile.date.clone().subtract(currentDateProfile.dateIncrement, 'days');
            view.setDate(newDate);
            var newStart = view.dateProfile.start;
            var newEnd = view.dateProfile.end;
            var eventManager = this.eventManager;
            if (eventManager.isOutOfLoadedRange(newStart, newEnd)) {
                //To Set new view's range update event manager's view  
                eventManager.view = view;
                //Fetch events
                //eventManager.buildEvents(); 
                eventManager.buildEventsFromFunction();// buildEvents includes view.render after loading events
            } else {
                //Clear left column, events data
                view.clearForNavMenu();
                
                view.render();
            }
        },
        next : function () {
            var view = this.view;
            var currentDateProfile = view.dateProfile;
            var newDate = currentDateProfile.date.clone().add(currentDateProfile.dateIncrement, 'days');
            view.setDate(newDate);
            
            var newStart = view.dateProfile.start;
            var newEnd = view.dateProfile.end;

            var eventManager = this.eventManager;
            if (eventManager.isOutOfLoadedRange(newStart, newEnd)) {
                //To Set new view's range update event manager's view  
                eventManager.view = view;
                //Fetch events
                //eventManager.buildEvents(); 
                eventManager.buildEventsFromFunction();// buildEvents includes view.render after loadin events
            } else {
                //Clear left column, events data
                view.clearForNavMenu();
                
                view.render();
            }
        },
        today : function () {
            this.gotoDate(moment());
        },
        gotoDate : function (date) {
            var view = this.view;
            var newDate = moment(date);
            view.setDate(newDate);
            var newStart = view.dateProfile.start;
            var newEnd = view.dateProfile.end;
            
            var eventManager = this.eventManager;
            //To Set new view's range update event manager's view  
            eventManager.view = view;
                
            //Reset data range
            eventManager.loadedRange = {start: newStart, end: newEnd};
            
            //Clear event data
            eventManager.clearEvents();
            
            //Fetch events
            //eventManager.buildEvents();
            eventManager.buildEventsFromFunction();// buildEvents includes view.render after loadin events
        },
        bindEventListenerToEl: function (el, name, handler) {
            //bind view menu
            $(el).off(name);
            $(el).on(name, handler);
        },
        loading: function() {
            elmStartLoading($(this.el));
        },
        unloading: function() {
            elmStopLoading($(this.el));
        },
        buildLoadMore: function(nextPage) {
            if (nextPage === undefined) {
                nextPage = 2;
            }
            if (nextPage == -1) {
                //Remove loadmore button
                if ($(this.el).find('#gic-load-more').length > 0) {
                    $(this.el).find('#gic-load-more').remove();
                }
            } else {
                if ($(this.el).find('#gic-load-more').length > 0) {
                    $(this.el).find('#gic-load-more').data('next-page', nextPage);
                } else {
                    $(this.el).find('#gic-bottom').append('<div id="gic-load-more" data-next-page="'+nextPage+'">Load More</div>');
                }
                
                //bind event listener
                var _this = this;
                _this.bindEventListenerToEl($('#gic-load-more'), 'click', function () {
                    _this.loadMore($(this).data('next-page'));
                });
            }
        },
        loadMore: function(nextPage) {
            var eventManager = this.eventManager;
            //@todo: queryid
            var isLoadMore = true;
            eventManager.buildEventsFromFunction({pageNumber : nextPage}, isLoadMore);
        },
        reset: function () {
            //@todo : reset data and others
        },
        destroy: function () {
            //@todo : destroy data and others
        },
    };

    function EventManager(cal) {
        var _this = this;
            _this.cal = cal;
            _this.view = cal.view;
            _this.options = cal.options;
            _this.allEvents = [];
            _this.newEvents = [];
            _this.allGroups = []; //left column assignees
            _this.newGroups = []; //left column assignees
            _this.func; //source function
            
        _this.init();
        return _this;
    }
    EventManager.prototype = {
        init : function() {
            //set event sources
            this.buildEventSources();
            
            //build events from event sources
            this.buildEvents();
            
            //build callback functions
            this.buildCallbackEventClick();
            this.buildCallbackEventRender();
        }, 
        buildEventSources: function () {
            var _this = this;
            var sources = [];
            var source;
            
            $.each(
                (this.options.events ? [ this.options.events ] : []).concat(this.options.eventSources || []),
                    function(i, sourceInput) {
                        source = _this.buildEventSource(sourceInput);
                        if (source) {
                            sources.push(source);
                        }
                    }
            );
    
            this.sources = sources;
        },
        buildEventSource : function (sourceInput) {
            var source;
            if ($.isFunction(sourceInput) || $.isArray(sourceInput)) {
                    source = { events: sourceInput };
            }
            //@todo
//            else if (typeof sourceInput === 'string') {
//                    source = { url: sourceInput };
//            }

            if (source) {
                return source;
            }
        },
        buildEvents : function () {
            var _this = this;
            var events = [];
            var sources  = this.sources;
            var sourceInput;
 
            for (var i=0; i<sources.length; i++) {
                sourceInput = sources[i].events;
//                if ($.isArray(sourceInput)) {
//                    //@todo: set up group by array
//                    // Array source 
//                    events = $.map(sourceInput, function(eventInput) {
//                        return _this.buildEventsFromArray(eventInput);
//                    });
//                    if (events) {
//                        _this.allEvents = (_this.allEvents? _this.allEvents:[]).concat(events);
//                        _this.newEvents = events;
//                    }
//                }
                
                if ($.isFunction(sourceInput)) {
                    _this.func = sourceInput;
                    _this.buildEventsFromFunction();
                }
                
            }
        },
        buildEventsFromFunction : function (params, isLoadMore) {
            if (this.func !== undefined) {
                // function source
                var _this = this;
                var view = this.view;
                var func = this.func;
                var events = [];
                var dateProfile = view.dateProfile;
//console.log('dateProfile in buildEventsFromFunction');    
//console.log(dateProfile);                
                var start = dateProfile.start.clone();
                var end = dateProfile.end.clone();
                var timezone = 'local'; //@todo
                if (isLoadMore === undefined) {
                    isLoadMore = false;
                }
                var groupName = view.getViewOptionsByKey('groupData');
                var curPage = 1;
                
                if (!isLoadMore) {
                    //Check current page and if current page is greater than 1, load the all pages
                    curPage = $('#gic-current-page').data('page');
                    if (parseInt(curPage) > 1) {
                        if (params === undefined) {
                            params = {};
                        } 
                        params.loadPages = curPage;
                    }
                }
                
                _this.cal.loading();
                func.call(this.cal, start, end, timezone, params, function (rawContentsArray) {
                    var rawEventArray;
                    var groups;
                    var nextPage = -1;// -1 no next page
//    console.log('after return function');                        
//    console.log(rawContentsArray);  
                    if (rawContentsArray.nextPage !== undefined) {
                        nextPage = rawContentsArray.nextPage;
                    }
                    if (rawContentsArray.curPage !== undefined) {
                        curPage = rawContentsArray.curPage;
                        $('#gic-current-page').data('page', curPage);
                        //$('#gic-current-page').text(curPage);//TEST
                    }
                    if (!isLoadMore) {
                        //Clear left column, events data
                        view.clearForNavMenu();
                        
                        //Update range
                        _this.updateLoadedRange(start, end);
                    }
                    if (rawContentsArray.events !== undefined) {
                        //If the mainContent has events key's value, set it as events
                        rawEventArray = rawContentsArray.events;
                        if (rawContentsArray[groupName] !== undefined) {
                            //Set left column group data if the mainContent has one
                            groups = rawContentsArray[groupName];
                            if (isLoadMore) {
                                _this.allGroups = (_this.allGroups? _this.allGroups:[]).concat(groups);
                            } else {
                                _this.allGroups = groups;
                                
                            }
                            _this.newGroups = groups;
                        }
                    } else {
                        //If the mainContent has no events key's value, set the mainContent as events
                        rawEventArray = rawContentsArray;
                    }
//console.log('rawEventArray');                    
//console.log(rawEventArray);
                    if (rawEventArray !== undefined && Array.isArray(rawEventArray)) {
                        //Build new events
                        for (var j=0; j<rawEventArray.length; j++) {
                            events.push(_this.buildEventsFromArray(rawEventArray[j]));
                        }
                        //Merge new events to allEvents
                        if (events) {
                            _this.mergeToAllEvents(events);
                            _this.newEvents = events;
                        }
                        //Event rendering
//                        if (curPage == 1) {
//                            view.render();
//                        } else {
//                            view.renderForReload();
//                        }
                            view.render(isLoadMore);
                        
                        //Build load more button
                        _this.cal.buildLoadMore(nextPage);
                    } else {
                        console.log('Error occurred while loading events!');
                    }

                    _this.cal.unloading();
                });
            }
        },
        buildEventsFromArray : function (input) {
            var out = {};
            var start, end;
            var allDay;

            // Copy all properties over to the resulting object.
            $.extend(out, input);

            out._id = input._id || ('_ev_' + eventGUID++ );

            if (out.resources) {
                    if (typeof out.resources == 'string') {
                        out.resources = out.resources.split(/\s+/);
                    }
                    if (typeof out.resources == 'array') {
                        //doing nothing
                    }
            } else {
                    out.resources = [];
            }

            start = input.start || input.date; // "date" is an alias for "start"
            end = input.end;
            
            //_dup_key is used when cheking duplication
            var groupId = this.view.getViewOptionsByKey('groupId');
            out._dup_key = input.id + '_' + input.start + '_' + input.end + ((input[groupId])?('_'+input[groupId]):'');

            // parse as a time (Duration) if applicable
            if (isTimeString(start)) {
                start = moment.duration(start);
            }
            if (isTimeString(end)) {
                end = moment.duration(end);
            }
            if (input.dow || moment.isDuration(start) || moment.isDuration(end)) {
                //@todo: Recurring events
                out.start = start ? moment.duration(start) : null; // will be a Duration or null
                out.end = end ? moment.duration(end) : null; // will be a Duration or null
                out._recurring = true; // our internal marker
            } else {
                if (start) {
                    start = moment(start);
                    if (!start.isValid()) {
                        return false;
                    }
                }

                if (end) {
                    end = moment(end);
                    if (!end.isValid()) {
                        end = null; // let defaults take over
                    }
                }
                
                //@todo: all day event
                allDay = input.allDay;
//                if (allDay === undefined) { // still undefined? fallback to default
//                    allDay = firstDefined(
//                            options.allDayDefault
//                    );
//                }
                this.assignDatesToEvent(start, end, allDay, out);
            }
            
            //@todo: normalize event
            //normalizeEvent(out); 

            return out;
        },
        assignDatesToEvent: function (start, end, allDay, event) {
            event.start = start;
            event.end = end;
            event.allDay = allDay;
            this.normalizeEventDates(event);
         },
        // Ensures proper values for allDay/start/end. Accepts an Event object, or a plain object with event-ish properties.
        normalizeEventDates: function (eventProps) {
            this.normalizeEventTimes(eventProps);
            if (eventProps.end && !eventProps.end.isAfter(eventProps.start)) {
                eventProps.end = null;
            }

            if (!eventProps.end) {
                eventProps.end = null;
            }
        },
        // Ensures the allDay property exists and the timeliness of the start/end dates are consistent
        normalizeEventTimes: function (eventProps) {
            //@todo
        },
        groupEventsById :function (groupId, isLoadMore) {
            //var allEvents = this.allEvents;
            if (isLoadMore) {
                var allEvents = this.newEvents;
            } else {
                var allEvents = this.allEvents;
            }
            
//console.log('all events in groupEventsById: is loadMOre?'+isLoadMore); 
//console.log(allEvents);    
            var eventsById = {};
            var event;
            if (allEvents !== undefined) {
                for (var i = 0; i < allEvents.length; i++) {
                    event = allEvents[i];
                    if (groupId === undefined) {
                        (eventsById[event._id] || (eventsById[event._id] = [])).push(event);
                    } else {
                        (eventsById[event[groupId]] || (eventsById[event[groupId]] = [])).push(event);
                    }
                }
            }
            
            return eventsById;
        },
        mergeToAllEvents : function (newEvents) {
            if (newEvents !== undefined && Array.isArray(newEvents)) {
                var allEventsLength = this.allEvents.length;
            
                if (allEventsLength == 0) {
                    this.allEvents = newEvents;
                } else {
                    //Filter events that are already in the allEvents array - in case of change a view(i.e. the day view) to a wider view(i.e. the week/month view)
                    var newEvent;
                    var isDuplicated;
                    //Consider duplication if all of id , start, end and groupId are same
                    for (var i=0; i<newEvents.length; i++) {
                        //@todo: find an easier way
                        newEvent = newEvents[i];
                        isDuplicated = false;
                        for (var j=0; j<allEventsLength; j++) {
                            if(this.allEvents[j]._dup_key === newEvent._dup_key) {
                                isDuplicated = true;
                                break;
                            }
                        }
                        if (!isDuplicated) {
                            this.allEvents.push(newEvent);
                        }
                    }
                }
            }
        },
        updateLoadedRange : function(start, end) {
            if (this.loadedRange === undefined) {
                this.loadedRange = {start: start, end: end};
            } else {
                var loadedRangeStart = this.loadedRange.start.clone();
                var loadedRangeEnd = this.loadedRange.end.clone();
                
                if (start.isBefore(loadedRangeStart)) {
                    //Set new start
                    this.loadedRange.start = start;
                } else if (end.isAfter(loadedRangeEnd)) {
                    //Set new end
                    this.loadedRange.end = end;
                }
            }
        },
        isOutOfLoadedRange : function(start, end) {
            // Check if the new start and end date is out of the loaded event start and end date range
            var isOutOfRange = false;
            if (this.loadedRange === undefined) {
                isOutOfRange = true;
            } else {
                var loadedRangeStart = this.loadedRange.start.clone(); 
                var loadedRangeEnd = this.loadedRange.end.clone(); 

//console.log('new start:' +start.format() + 'new end:' +end.format());                
//console.log('loadedRangeStart start:' +loadedRangeStart.format() + 'loadedRangeEnd end:' +loadedRangeEnd.format());                     
                if (start.isBefore(loadedRangeStart) || end.isAfter(loadedRangeEnd)) {
                    isOutOfRange = true;
                }
            }
            if (isOutOfRange) {
                this.updateLoadedRange(start, end);
            }
            
            return isOutOfRange;
	},
        buildCallbackEventClick : function () {
            var sourceInput = this.options.eventClick;
            
            if ($.isFunction(sourceInput)) {
                this.eventClickFunc = sourceInput;
            }
        },
        buildCallbackEventRender : function () {
            var sourceInput = this.options.eventRender;
            
            if ($.isFunction(sourceInput)) {
                this.eventRenderFunc = sourceInput;
            }
        },
        callbackEventClick : function (event, el, view) {
             var func = this.eventClickFunc;
             func.call(this.cal, event, el, view);
        },
        callbackEventRender : function (event, el, view) {
             var func = this.eventRenderFunc;
             func.call(this.cal, event, el, view);
        }, 
        clearEvents : function() {
            this.allEvents = [];
        }
    };
  
    function DateProfileGenerator(view) {
        var _this = this;
            _this.view = view;
        return _this;
    }
    DateProfileGenerator.prototype = {
        build : function (date, direction) {
            var options = this.view.options;
            if (direction === undefined) {
                direction = 0;
            }
            var timeUnit = this.view.timeUnit;
            var firstDay = options.firstDay;
            var currentRangeInfo = this.buildCurrentRangeInfo(date, direction, timeUnit, firstDay);
            var newDate = currentRangeInfo.date;
            var dateIncrement = this.getDateIncrement();
            var minTime = options.minTime;
            var maxTime = options.maxTime;
            //Calculate biz hour duration
 
            var newDateText = newDate.clone().format('YYYY-MM-DD');
            var minTimeModel = moment(newDateText + ' '+ minTime);
            var maxTimeModel = moment(newDateText + ' '+ maxTime);
            
            var minHourDiff = moment.duration(minTimeModel.diff(moment(newDateText + ' '+ '00:00:00')));
            var maxHourDiff = moment.duration(moment(newDateText + ' '+ '24:00:00').diff(maxTimeModel));
            
            var beforeBizHourDuration = Math.round(minHourDiff.asHours());
            var afterBizHourDuration = Math.round(maxHourDiff.asHours());
            var bizHourDuration = 24 - beforeBizHourDuration - afterBizHourDuration;
            
            return {
                date: newDate,
                timeUnit: timeUnit, //week, day for a time row 
                dateIncrement: dateIncrement, //dateIncrement when prev/next execution
                start: currentRangeInfo.start,
                end: currentRangeInfo.end,
                minTime: minTime,
                maxTime: maxTime,
                beforeBizHourDuration: beforeBizHourDuration,
                afterBizHourDuration: afterBizHourDuration,
                bizHourDuration: bizHourDuration,
                firstDay: firstDay, 
            };
        },
        // Builds a structure with info about the "current" range, the range that is
        buildCurrentRangeInfo : function (date, direction, timeUnit, firstDay) {
            var start;
            var end;
            var newDate = date.clone().add(direction, timeUnit);
            var startOfUnit;
            var endOfUnit;
            startOfUnit = newDate.clone().startOf(timeUnit);
            if(timeUnit === 'month') {
                //Get the first date of the week of the month's start date
                start = startOfUnit.clone().startOf('week');
            } else {
                start = startOfUnit;
            }
            if ((timeUnit === 'week' || timeUnit === 'month') && firstDay > 0) {
                start = start.add(firstDay, 'days');
                if (start.isAfter(newDate)) {
                    //Because of firstDay adjustment
                    start.add(-1, timeUnit);
                }
            } 

            if(timeUnit === 'month') {
                //Get the last day of the month
                endOfUnit = newDate.clone().endOf(timeUnit);
                //Get the end of the week of the month's last day
                end = endOfUnit.clone().endOf('week');
                if (firstDay > 0) {
                    end = end.add(firstDay, 'days');
                } 
            } else {
                end = start.clone().add(1, timeUnit);
            }

            return { date: newDate, start: start, end: end };
        },
        getDateIncrement : function () {
            var viewType = this.view.viewType;
            if (viewType == 'week') {
                return 7;
            } else if (viewType == 'day') {
                return 1;
            } else {
                //@todo monthly
                return 30;
            }
        },
    };

    
    function CalView(cal, viewType, date) {
        var _this = this;
            _this.cal = cal;
            _this.el = cal.el;
            _this.viewType = viewType;
            _this.options = cal.options;
            _this.viewEvents = [];
            _this.viewGroups = [];
        
        //Set view type
//        if (_this.viewType === undefined) {
//            _this.viewType = _this.options.defaultView;
//        }
        _this.init();
        
        //Set date profile generator to manage date range
        _this.dateProfileGenerator = new DateProfileGenerator(_this);
        //Set timeUnit for a calender depending on view type
        _this.setTimeUnit();
        _this.setDate(date);
        return _this;
    }
    CalView.prototype = {
        init: function () {
            $(this.el).find('#gic-body').append('<div class="gic-view view-type-'+this.viewType+'"></div>');
            this.renderSkeleton();
        },
        setTimeUnit : function () {
            var viewType = this.viewType;
            var timeUnit;
            //Set timeUnit for a calender 
            if (viewType == 'day') {
                timeUnit = 'day';
            } else if (viewType == 'month') {
                timeUnit = 'month';
            } else {
                timeUnit = 'week';
            }
            this.timeUnit = timeUnit;
        },
        setDate : function (date) {
            //Set current date and dateProfile
            if (date === undefined) {
                date = moment();
            } else {
                date = moment(date);
            }
            this.dateProfile = this.dateProfileGenerator.build(date);
//console.log('this.dateProfile in setDate');              
//console.log(this.dateProfile);            
        },
        buildViewEvents : function (isLoadMore) {
            //Build events for each view
            
            var eventManager = this.cal.eventManager;
            var groupId = this.getViewOptionsByKey('groupId');
//console.log('groupId:'+groupId);            
 
            //Set groups to render them to left column slots
            var viewEvents;
            if (groupId === undefined) {
                //Group events by event _id: month
                viewEvents = eventManager.groupEventsById(undefined, isLoadMore);
                this.viewGroups = null;
            } else {
                //Group events by groupId: week, day
                viewEvents = eventManager.groupEventsById(groupId, isLoadMore);
                if (isLoadMore) {
                    this.viewGroups = eventManager.newGroups;

                } else {
                    this.viewGroups = eventManager.allGroups;
                }
            }
            this.viewEvents = viewEvents;
            
//console.log('this.viewEvents');            
//console.log(this.viewEvents);
//console.log('this.viewGroups');            
//console.log(this.viewGroups);
        },
        // Get view option by view type and key
        getViewOptionsByKey: function (searchKey) {
            var viewType = this.viewType;
            var viewsOptions = this.options.views;
            var optionValue;
            if (viewsOptions !== undefined) {
                for(var type in viewsOptions){
                    if (type == viewType) {
                        optionValue = viewsOptions[type][searchKey];
                        break;
                    }
                }
            }
            return optionValue;
        },
        updateTitle: function() {
            var titleEl = $('#gic-title');
            var viewType = this.viewType;
            var options = this.options;
            var defaultTitle = this.cal.title;
            if (defaultTitle !== undefined || viewType === undefined) {
                titleEl.html(defaultTitle);
            } else {
                var currentDateProfile = this.dateProfile;
                if (viewType === 'day') {
                    titleEl.html(currentDateProfile.date.clone().format(options.titleDateFormat));
                } else if (viewType === 'month') {
                    titleEl.html(currentDateProfile.date.clone().format(options.titleMonthFormat));
                } else {
                    // week
                    var start = currentDateProfile.start.clone();
                    var end = currentDateProfile.end.clone();
                    var startYear = start.format('YYYY');
                    var startDate = start.format(options.titleDateFormat);
                    var endYear = end.format('YYYY');
                    var endDate = end.format(options.titleDateFormat);
                    
                    var title = startDate;
                    if (startYear !== endYear) {
                        title += ' ' + startYear;
                    }
                    title += ' - ' + endDate + ' ' + endYear;
                    titleEl.html(title);
                }
            }
        },
        render: function(isLoadMore) {
//console.log('view render isLoadMore' + isLoadMore);            
            //build events for each view
            this.buildViewEvents(isLoadMore); 

            // build HTML
            if (isLoadMore) {
                //Load more
                this.renderColumn(); 
                this.renderBody();
            } else {
                this.renderHeader();
                this.renderColumn(); 
                this.renderBody();
                
                // update title
                this.updateTitle();
            }
        },
        renderSkeleton: function () {
            $(this.el).find('.gic-view').append(this.renderSkeletonHtml());
        },
        renderSkeletonHtml : function () {
            if (this.viewType == 'month') {
                //month
                return '' +
                '<table class="gic-table" border="0" cellpadding="0" cellspacing="0">' +
                    '<thead class="gic-head">' +
                        '<tr>' +
                            '<td class="gic-head-container">'+
                                '<table class="gic-head-table" border="0" cellpadding="0" cellspacing="0">' +
                                    '<tr>' +
                                        '<td class="gic-head-content">'+
                                        '</td>' +
                                    '</tr>' +
                                '</table>' +
                            '</td>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody class="gic-body">' +
                        '<tr>' +
                            '<td class="gic-body-container">'+
                                '<table class="gic-body-table" border="0" cellpadding="0" cellspacing="0">' +
                                '</table>' +
                            '</td>' +
                        '</tr>' +
                    '</tbody>' +
                '</table>';
            } else {
                //week, day: a view with left column
                return '' +
                '<table class="gic-table" border="0" cellpadding="0" cellspacing="0">' +
                    '<thead class="gic-head">' +
                        '<tr>' +
                            '<td class="gic-head-container">'+
                                '<table class="gic-head-table" border="0" cellpadding="0" cellspacing="0">' +
                                    '<tr>' +
                                        '<td class="gic-head-content-col left-col">'+
                                        '</td>' +
                                        '<td class="gic-head-content">'+
                                        '</td>' +
                                    '</tr>' +
                                '</table>' +
                            '</td>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody class="gic-body">' +
                        '<tr>' +
                            '<td class="gic-body-container">'+
                                '<table class="gic-body-table" border="0" cellpadding="0" cellspacing="0">' +
                                '</table>' +
                            '</td>' +
                        '</tr>' +
                    '</tbody>' +
                '</table>';
            }
        },
        renderHeader: function () {
            //Header slots
            
            var $headContentEl = $(this.el).find('.gic-head-content');
            //Clear Header
            $headContentEl.html('');
            
            if (this.viewType == 'day') {
                var bizHourDuration = this.dateProfile.bizHourDuration;
                var hoursToggleHTML = '';
                if (bizHourDuration < 24) {
                    hoursToggleHTML = '<span class="hours_toggle" title="Expand/contract business hours"><span class="icon light_gray expand"></span></span>';
                 }
                $headContentEl.append(hoursToggleHTML+'<div class="gic-head-content-inner" style="'+this.getBussinessHourStyle()+'"></div>');
                
                if (bizHourDuration < 24) {
                    var $hoursToggleEl = $headContentEl.find('.hours_toggle');
                    
                    this.cal.bindEventListenerToEl($hoursToggleEl, 'click', function () {
                        var $gicTableEl = $(this).closest('.gic-table');
//console.log($gicTableEl); 
                        if ($gicTableEl.hasClass('expand')) {
                            $gicTableEl.removeClass('expand');
                        } else {
                            $gicTableEl.addClass('expand');
                        }
                    });
                }
                
            } else {
                $headContentEl.append('<div class="gic-head-content-inner"></div>');
            }
            
            var $parentEl = $(this.el).find('.gic-head-content-inner');
            //Set Grid
            if (this.viewType == 'day') {
                this.headerGrid = this.instantiateTimeGrid();
            } else if (this.viewType == 'week') {
                //week
                this.headerGrid = this.instantiateDayGrid();
            } else if (this.viewType == 'month') {
                this.headerGrid = this.instantiateWeekDayGrid();
            }
            this.headerGrid.renderGrids($parentEl, 'header');
        },
        renderColumn: function () {
            if (this.viewType == 'week' || this.viewType == 'day') {
                //Column slots
                if (this.columnGrid === undefined) {
                    this.columnGrid = this.instantiateGroupGrid();
                } else {
                    this.columnGrid.updateGridTable();
                }
                var $parentEl = $(this.el).find('.gic-body-table');
                this.columnGrid.renderGrids($parentEl, 'column');
            }
        },
        
        renderBody: function () {
            var $parentEl = $(this.el).find('.gic-body-table');
            
            if (this.viewType == 'month') {
                if (this.dayGrid === undefined) {
                    this.dayGrid = this.instantiateDayGrid();
                } else {
                    this.dayGrid.updateGridTable();
                }
                this.dayGrid.renderGrids($parentEl, 'body');
            }
            
            //Body slots
            if (this.eventGrid === undefined) {
                this.eventGrid = this.instantiateEventGrid();
            } else {
                this.eventGrid.updateGridTable();
            }
            
            this.eventGrid.renderGrids($parentEl, 'body');
        },
        instantiateTimeGrid : function () {
            return new TimeGrid(this);
        },
        instantiateDayGrid : function () {
            return new DayGrid(this);
        },
        instantiateWeekDayGrid : function () {
            return new WeekDayGrid(this);
        },
        instantiateGroupGrid : function () {
            return new GroupGrid(this);
        },
        instantiateEventGrid : function () {
            return new EventGrid(this);
        },
//        setEvents: function(events) {
//            //@todo
//	},
//	unsetEvents: function() {
//            //@todo
//	},
        clear: function () {
            //Clear view HTML
            this.clearView();
            
            //Clear data
            this.clearData();
        },
        clearForNavMenu: function () {
            //Clear body
            $(this.el).find('.gic-body-table').html('');
            
            //Clear data
            this.clearData();
        },
        clearView: function () {
            //Clear view HTML
            $(this.el).find('.gic-view').html('');
        },
        clearData: function () {
            //Clear data
            this.viewGroups = [];
            this.viewEvents = [];
        },
        getBussinessHourStyle : function () {
            var bizHourDuration = this.dateProfile.bizHourDuration;
            
            /** min/max time 
            width = 100%x24hr/bizhour
            left = width/24*left
            i.e. biz-hour-width-10 = 100%x24/10 = 240%;
                 biz-hour-width-8 = -100%x24/10/24 * 8 = -80%;
            **/

            var style = '';
            var width = '100%';
            var left = '0';
            if (bizHourDuration < 24) {
                //get extended width
                width = Math.round(24/bizHourDuration*10000.0)/100;
                style +='width:'+width+'%;';
                
                //get left position
                left = Math.round(width/24*100.0)/100 * this.dateProfile.beforeBizHourDuration;
                style +='left:-'+left+'%;';
            }
             
            return style;
        },
        destroy: function () {
            $(this.el).find('.gic-view').remove();
            this.clearData();
        },
    };
    
    function EventGrid(view) {
        var _this = this;
            _this.view = view;
        _this.init();
        return _this;
    }
    EventGrid.prototype = {
        init : function () {
            this.updateGridTable();
        },
        // Populates events
	updateGridTable: function() {
            var dateProfile = this.view.dateProfile;
            this.start = dateProfile.start;
            this.end = dateProfile.end;
            var viewType = this.view.viewType;
            var viewGroups = this.view.viewGroups;
            var viewEvents = this.view.viewEvents;
//console.log('viewEvents in updateGridTable of EventGrid');  
//console.log(viewEvents);  
            if (viewType == 'week') {
                if (viewGroups !== undefined && viewGroups.length > 0 ) {
                    //rows : groups, cols : days
                    var date = this.start.clone();
                    var gridDataArray = [];
                    var colDataArray = [];
                    var gridPerRow;
                    var rowCnt = viewGroups.length;
                    while (date.isBefore(this.end)) {
                        colDataArray.push(date.clone());
                        date.add(1, 'days');
                    }
                    gridPerRow = colDataArray.length;
                    
                    var viewGroup;
                    var colData;
                    var colDataStart;
                    var colDataEnd;
                    var groupEvents;
                    var event;
                    var row;
                    var col;
                    for (row=0; row<rowCnt; row++) {
                        viewGroup = viewGroups[row];
                        groupEvents = viewEvents[viewGroup.id];
                        if (groupEvents !== undefined && groupEvents.length > 0) {
                            for (col=0; col<gridPerRow; col++) {
                                colData = colDataArray[col];
                                colDataStart = colData.clone();
                                colDataEnd = colDataStart.clone().add(1, 'days');
                                for (var i=0; i<groupEvents.length; i++) {
                                    event = groupEvents[i];
                                    //@todo: cross midnight events, max time, min time
//console.log('colDataStart'+ colDataStart.format()+'/event.start'+event.start.format()+'colDataEnd'+ colDataEnd.format()+'/event.end'+event.end.format());  
                                    if (colDataStart.isSameOrBefore(event.start) &&
                                        colDataEnd.isSameOrAfter(event.end)) {
                                        //gridDataArray.push({'posId': 'r-'+row+'-c-'+col, 'event': event});
                                        gridDataArray.push({'posId': 'id-'+viewGroup.id+'-c-'+col, 'event': event});
                                    }
                                }
                            }
                        }
                    }
                    this.gridDataArray = gridDataArray;
                    this.rowCnt = rowCnt;
                    this.colCnt = gridPerRow;
                } else {
                    //@todo
                }
               
            } else if (viewType == 'day') {
                //@todo
                if (viewGroups !== undefined && viewGroups.length > 0 ) {
                    //rows : groups, cols : hours
                    var date = this.start.clone();
                    var gridDataArray = [];
                    var colDataArray = [];
                    var gridPerRow;
                    var rowCnt = viewGroups.length;
                    while (date.isBefore(this.end)) {
                        colDataArray.push(date.clone());
                        date.add(1, 'hours');
                    }
                    gridPerRow = colDataArray.length;
                    
                    var viewGroup;
                    var colData;
                    var colDataStart;
                    var colDataEnd;
                    var groupEvents;
                    var event;
                    var row;
                    var col;
                    var segLeft;
                    var segWidth;
                    var leftDuration;
                    var widthDuration;
                    var segFullWidth = gridPerRow; //@todo : max - min hours
                    //To avoid duplication because of endtime is 00:00, subtract -1 sec
                    var endTime = this.end.clone().subtract(1, 'seconds');
                    for (row=0; row<rowCnt; row++) {
                        viewGroup = viewGroups[row];
                        groupEvents = viewEvents[viewGroup.id];
                        if (groupEvents !== undefined && groupEvents.length > 0) {
                            for (var i=0; i<groupEvents.length; i++) {
                                event = groupEvents[i];
                                if (event.start.isSameOrBefore(this.start) && event.end.isSameOrAfter(endTime)) {
                                    // event.start <  zone < event.end
                                    segLeft =  0;
                                    segWidth = segFullWidth;
                                    gridDataArray.push({'posId': 'id-'+viewGroup.id, 'segLeft': segLeft, 'segWidth': segWidth, 'event': event});
                                } else if (event.start.isSameOrAfter(this.start) && event.end.isSameOrBefore(endTime)) {
                                    // zone.start <  event < zone.end
                                    leftDuration = moment.duration(event.start.diff(this.start));
                                    segLeft =  Math.round(leftDuration.asHours() * 2)/2; // 30min
                                    widthDuration = moment.duration(event.end.diff(event.start));
                                    segWidth = Math.round(widthDuration.asHours() * 2)/2; // 30min
                                    gridDataArray.push({'posId': 'id-'+viewGroup.id, 'segLeft': segLeft, 'segWidth': segWidth, 'event': event});
                                } else if (event.start.isSameOrBefore(this.start) && event.end.isSameOrAfter(this.start) && event.end.isSameOrBefore(endTime)) {
                                    // event.start <  zone.start < event.start <zone.end
                                    segLeft =  0;
                                    widthDuration = moment.duration(event.end.diff(this.start));
                                    segWidth = Math.round(widthDuration.asHours() * 2)/2; // 30min
                                    gridDataArray.push({'posId': 'id-'+viewGroup.id, 'segLeft': segLeft, 'segWidth': segWidth, 'event': event});
                                } else if (event.start.isSameOrAfter(this.start) && event.start.isSameOrBefore(endTime) && event.end.isSameOrAfter(endTime)) {
                                    // zone.start <  event.start < zone.start <event.end
                                    leftDuration = moment.duration(event.start.diff(this.start));
                                    segLeft =  Math.round(leftDuration.asHours() * 2)/2; // 30min
                                    widthDuration = moment.duration(endTime.diff(event.start));
                                    segWidth = Math.round(widthDuration.asHours() * 2)/2; // 30min
                                    gridDataArray.push({'posId': 'id-'+viewGroup.id, 'segLeft': segLeft, 'segWidth': segWidth, 'event': event});
                                }
                            }
                        }
                    }
                    //@todo : sorting
                    this.gridDataArray = gridDataArray;
                    this.rowCnt = rowCnt;
                    this.colCnt = gridPerRow;
                } else {
                    //@todo
                }
            } else if (viewType == 'month') {
                var date = this.start.clone();
                var gridDataArray = [];
                for (var i=0; i<viewEvents.length; i++) {
                    event = viewEvents[i];
                    //@todo: cross midnight events
                    //       muntil day events
                    gridDataArray.push({'posId': 'id-cell-'+event.start.format('MM-D'), 'event': event});
                }
                this.gridDataArray = gridDataArray;
                this.rowCnt = rowCnt;
                this.colCnt = gridPerRow;
            }
	},
        renderGrids : function ($parentEl, gridType) {
            var gridDataArray = this.gridDataArray;
            if (gridDataArray !== undefined) {
                var viewType = this.view.viewType;
                var gridData;
                var $eventEl;
                var posId;

                //Build tr, td tags for all grids
                this.renderBodySkeletonHtml($parentEl);
                if (viewType == 'week') {
                    //Render grids to each cell
                    for (var i=0; i<gridDataArray.length; i++) {
                        posId = gridDataArray[i].posId;
                        if (posId !== undefined) {
                            $eventEl = $('#'+posId);
                            gridData = gridDataArray[i];
                            if (gridData !== undefined) {
                                if (gridType == 'header') {
                                    this.renderHeadGridHtml($eventEl, gridData);
                                } else if (gridType == 'body') {
                                    this.renderBodyGridHtml($eventEl, gridData);
                                } else if (gridType == 'column') {
                                    this.renderColumnGridHtml($eventEl, gridData);
                                }
                            }
                            
                        }
                    }
                } else if (viewType == 'day') {
                    //Render grids to each row grid
                    for (var i=0; i<gridDataArray.length; i++) {
                        posId = gridDataArray[i].posId;
                        if (posId !== undefined) {
                            $eventEl = $('#'+posId);
                            gridData = gridDataArray[i];
                            if (gridData !== undefined) {
                                if (gridType == 'header') {
                                    this.renderHeadGridHtml($eventEl, gridData);
                                } else if (gridType == 'body') {
                                    this.renderBodyGridHtml($eventEl, gridData);
                                } else if (gridType == 'column') {
                                    this.renderColumnGridHtml($eventEl, gridData);
                                }
                            }
                        }
                    }
                } else if (viewType == 'month') {
                    //Render grids to each cell
                    for (var i=0; i<gridDataArray.length; i++) {
                        posId = gridDataArray[i].posId;
                        if (posId !== undefined) {
                            $eventEl = $('#'+posId);
                            gridData = gridDataArray[i];
                            if (gridData !== undefined) {
                                if (gridType == 'header') {
                                    this.renderHeadGridHtml($eventEl, gridData);
                                } else if (gridType == 'body') {
                                    this.renderBodyGridHtml($eventEl, gridData);
                                } else if (gridType == 'column') {
                                    this.renderColumnGridHtml($eventEl, gridData);
                                }
                            }
                        }
                    }
                }
            }
            
            
        },
        renderBodySkeletonHtml: function($parentEl) {
            var viewType = this.view.viewType;
            var rowCnt = this.rowCnt;
            var colCnt = this.colCnt;
            var viewGroups = this.view.viewGroups;
            var viewGroup;
            var viewGroupId;
            var row;
            var col;
            var $eventTr;
            var $eventBodyBg;
            var $eventTd;
            var date = this.start.clone();
            if (viewType == 'week') {
                // build html tags
                for (row = 0; row < rowCnt; row++) {
                    //add event td
                    viewGroup = viewGroups[row];
                    if (viewGroup !== undefined) {
                        viewGroupId = viewGroup.id;
                        $eventTr = $parentEl.find('#group-grid-'+ viewGroupId);
                        if ($eventTr.find('.gic-td-event-body').length == 0) {
                            $eventTr.append('<td class="gic-td-event-body"><div class="gic-td-event-body-bg"></div></td>');
                            $eventTd = $eventTr.find('.gic-td-event-body');
                            $eventBodyBg = $eventTd.find('.gic-td-event-body-bg');
                            for (col = 0; col < colCnt; col++) {
                                $eventBodyBg.append('<div class="bg-cell col-1-'+colCnt+'"></div>');
                                //$eventTd.append('<div class="gic-td-event-cell inline-col empty col-1-'+colCnt+'" id="r-'+row+'-c-'+col+'"></div>');
                                $eventTd.append('<div class="gic-td-event-cell inline-col empty col-1-'+colCnt+'" id="id-'+viewGroupId+'-c-'+col+'"></div>');

                            }
                        }
                    }
                }
            } else if (viewType == 'day') {
                // build html tags
                for (row = 0; row < rowCnt; row++) {
                    //add event td
                    viewGroup = viewGroups[row];
                    if (viewGroup !== undefined) {
                        viewGroupId = viewGroup.id;
                        $eventTr = $parentEl.find('#group-grid-'+ viewGroupId);
                        if ($eventTr.find('.gic-td-event-body').length == 0) {
                            
                            $eventTr.append('<td class="gic-td-event-body"><div class="gic-td-event-body-inner"><div class="gic-td-event-body-bg" style="'+this.view.getBussinessHourStyle()+'"></div></td>');
                            //$eventTd = $eventTr.find('.gic-td-event-body');
                            $eventTd = $eventTr.find('.gic-td-event-body-inner');
                            $eventBodyBg = $eventTd.find('.gic-td-event-body-bg');
                            //$eventTd.append('<div class="gic-td-event-seg empty" id="r-'+row+'"></div>');
                            $eventTd.append('<div class="gic-td-event-seg empty" id="id-'+viewGroupId+'" style="'+this.view.getBussinessHourStyle()+'"></div>');
                            for (col = 0; col < colCnt; col++) {
                                $eventBodyBg.append('<div class="bg-cell col-1-'+colCnt+'"></div>');
                            }
                        }
                    }
                }
            } else {
                //@todo
            }
            
	},
        renderHeadGridHtml: function($parentEl, gridData) {
            //@todo
	},
        renderColumnGridHtml : function ($parentEl, gridData) {
            //@todo
        },
        renderBodyGridHtml : function ($parentEl, gridData) {
            var viewType = this.view.viewType;
            var event = gridData.event;
            var id = event.id;
            var uid = event._id;
            var title = event.title;
            var color = event.color;
            if (color === undefined) {
                color = this.view.options.color;
            }
            var timeFormat = this.view.options.timeFormat;
            var datetimeFormat = this.view.options.datetimeFormat;
            var startDatetime = event.start.format(datetimeFormat);
            var endDatetime = event.end.format(datetimeFormat);
            var classNameArray = event.className;
            var classes = '';
            if (classNameArray !== undefined) {
                classes = classNameArray.join(' ');
            }
            var html;
            var bodyContents = this.view.getViewOptionsByKey('bodyContents');
            if (viewType == 'week') {
                classes += getSingleDayGridClasses(event.start);
                html = '<div data-uid="'+uid+'" data-id="'+id+'" data-start="'+startDatetime+'" data-end="'+endDatetime+'" class="gic-seg gic-event-cell '+classes+'">' +
                        '<div class="gic-bg" style="background-color:'+color+';"></div>' +
                        '<div class="gic-content">';
                
                var key;
                for(var i=0; i<bodyContents.length; i++ ) {
                    key = bodyContents[i];
                    if (event[key] !== undefined) {
                        if (key === 'start' || key === 'end') {
                            html += '<div class="gic-'+key+'"><span class="gic-content-title">'+ key + '</span> ' +event[key].format(timeFormat) +'</div>';
                        } else {
                            html += '<div class="gic-'+key+'"><span class="gic-content-title">'+ key + '</span> ' +event[key] +'</div>';
                        }
                    }
                }
                
                html += '</div>' +
                    '</div>';
            } else if (viewType == 'day') {
                var segLeft = gridData.segLeft;
                var segWidth = gridData.segWidth;
                var styleLeftPercent = Math.round(segLeft/24*10000.0)/100;
                var styleWidthPercent = Math.round(segWidth/24*10000.0)/100;
                html = '<div data-uid="'+uid+'" data-id="'+id+'" data-start="'+startDatetime+'" data-end="'+endDatetime+'" style="left:'+styleLeftPercent+'%;width:'+styleWidthPercent+'%;" class="gic-seg gic-event-range '+classes+'">' +
                        '<div class="gic-bg" style="background-color:'+color+';"></div>' +
                        '<div class="gic-content">';
                var key;
                for(var i=0; i<bodyContents.length; i++ ) {
                    key = bodyContents[i];
                    if (key === 'start' || key === 'end') {
                        html += '<div class="gic-'+key+'"><span class="gic-content-title">'+ key + '</span> '+ event[key].format(timeFormat); +'</div>';
                    } else {
                        html += '<div class="gic-'+key+'"><span class="gic-content-title">'+ key + '</span> '+ event[key] +'</div>';
                    }
                }
                html += '</div>' +
                    '</div>';
            } else if (viewType == 'month') {
//                var segLeft = gridData.segLeft;
//                var segWidth = gridData.segWidth;
//                var styleLeftPercent = Math.round(segLeft/24*10000.0)/100;
//                var styleWidthPercent = Math.round(segWidth/24*10000.0)/100;
//                html = '<div data-uid="'+uid+'" data-id="'+id+'" data-start="'+startDatetime+'" data-end="'+endDatetime+'" style="left:'+styleLeftPercent+'%;width:'+styleWidthPercent+'%;" class="gic-seg gic-event-range '+classes+'">' +
//                        '<div class="gic-bg" style="background-color:'+color+';"></div>' +
//                        '<div class="gic-content">';
//                var key;
//                for(var i=0; i<bodyContents.length; i++ ) {
//                    key = bodyContents[i];
//                    if (key === 'start' || key === 'end') {
//                        html += '<div class="gic-'+key+'"><span class="gic-content-title">'+ key + '</span> '+ event[key].format(timeFormat); +'</div>';
//                    } else {
//                        html += '<div class="gic-'+key+'"><span class="gic-content-title">'+ key + '</span> '+ event[key] +'</div>';
//                    }
//                }
//                html += '</div>' +
//                    '</div>';
            }
            $parentEl.append(html);
            $parentEl.removeClass('empty');
            
            //Bind event listener
            this.bindEventListenersToEvent(event, $parentEl.find('.gic-seg')[0]);
        },
        bindEventListenersToEvent : function (event, $eventEl) {
            //bind event click
            var cal = this.view.cal;
            var view = this.view
            cal.bindEventListenerToEl($eventEl, 'click', function (e) {
                cal.eventManager.callbackEventClick(event, e, view);
            });
            
            //bind event render
            cal.eventManager.callbackEventRender(event, $eventEl, view);
        },
        
        
    };
    
    
    function TimeGrid(view) {
        var _this = this;
            _this.view = view;
        _this.init();
        return _this;
    }
    TimeGrid.prototype = {
        init : function () {
            this.dateProfile = this.view.dateProfile;
            this.start = this.dateProfile.start;
            this.end = this.dateProfile.end;
            this.breakOnWeeks = false; //@todo: for now false because it's for week/day view now
            this.updateGridTable();
        },
        // Populates dates
	updateGridTable: function() {
            var date = this.start.clone();
            var gridDataArray = [];
            var gridPerRow;
            var rowCnt;
            while (date.isBefore(this.end)) {
                gridDataArray.push(date.clone());
                date.add(1, 'hours');
            }
            gridPerRow = gridDataArray.length;
            rowCnt = 1;

            this.gridDataArray = gridDataArray;
            this.rowCnt = rowCnt;
            this.colCnt = gridPerRow;
	},
        renderGrids : function ($parentEl, gridType) {
            var gridDataArray = this.gridDataArray;
            if (gridDataArray !== undefined) {
                var rowCnt = this.rowCnt;
                var colCnt = this.colCnt;
                var gridDataIndex = 0;
                var gridData;
                var row;
                var col;

                // trigger dayRender with each cell's element
                for (row = 0; row < rowCnt; row++) {
                    for (col = 0; col < colCnt; col++) {
                        gridData = gridDataArray[gridDataIndex++];
                        if (gridData !== undefined) {
                            if (gridType == 'header') {
                                this.renderHeadGridHtml($parentEl, gridData);
                            } else if (gridType == 'body') {
                                this.renderBodyGridHtml($parentEl, gridData);
                            } else if (gridType == 'column') {
                                this.renderColumnGridHtml($parentEl, gridData);
                            }
                        }
                    }
                }
            }
            
        },
        renderHeadGridHtml: function($parentEl, gridData) {
            var timeFormat = this.view.options.headerTimeFormat;
            var html = '<div class="gic-col gic-time-col inline-col col-1-'+this.colCnt+'">' +
                        '<div class="gic-bg"></div>' +
                        '<div class="gic-content">'+gridData.format(timeFormat)+'</div>' +
                    '</div>';
            $parentEl.append(html);
	},
        renderColumnGridHtml : function ($parentEl, gridData) {
            var timeFormat = this.view.options.timeFormat;
            var html = '<div class="gic-row gic-day-row">' +
                    '<div class="gic-bg"></div>' +
                    '<div class="gic-content">'+gridData.format(timeFormat)+'</div>' +
                '</div>';
            $parentEl.append(html);
        },
        renderBodyGridHtml : function ($parentEl, gridData) {
            var timeFormat = this.view.options.timeFormat;
            var html = '<div class="gic-col gic-body-col">' +
                    '<div class="gic-bg"></div>' +
                    '<div class="gic-content">'+gridData.format(timeFormat)+'</div>' +
                '</div>';
            $parentEl.append(html);
        },
    };
    
    function DayGrid(view) {
        var _this = this;
            _this.view = view;
        _this.init();
        return _this;
    }
    DayGrid.prototype = {
        init : function () {
            this.dateProfile = this.view.dateProfile;
            this.start = this.dateProfile.start;
            this.end = this.dateProfile.end;
            this.updateGridTable();
        },
        // Populates dates
	updateGridTable: function() {
            var date = this.start.clone();
            var gridDataArray = [];
            var gridPerRow;
            var rowCnt;
            while (date.isBefore(this.end)) {
                gridDataArray.push(date.clone());
                date.add(1, 'days');
            }
            if (this.view.viewType == 'month') {
                gridPerRow = 7;
                rowCnt = Math.ceil(gridDataArray.length / gridPerRow);
            } else {
                gridPerRow = gridDataArray.length;
                rowCnt = 1;
            }
            this.gridDataArray = gridDataArray;
            this.rowCnt = rowCnt;
            this.colCnt = gridPerRow;
	},
        renderGrids : function ($parentEl, gridType) {
            var gridDataArray = this.gridDataArray;
            if (gridDataArray !== undefined) {
                var rowCnt = this.rowCnt;
                var colCnt = this.colCnt;
                var gridDataIndex = 0;
                var gridData;
                var row;
                var col;
                var $rowEl

                // trigger dayRender with each cell's element
                for (row = 0; row < rowCnt; row++) {
                    if (gridType == 'body') {
                        this.renderBodyRowHtml($parentEl, row);
                    }
                    for (col = 0; col < colCnt; col++) {
                        gridData = gridDataArray[gridDataIndex++];
                        if (gridData !== undefined) {
                            if (gridType == 'header') {
                                this.renderHeadGridHtml($parentEl, gridData);
                            } else if (gridType == 'body') {
                                $rowEl = $parentEl.find('#day-row-'+row);
                                this.renderBodyGridHtml($rowEl, gridData);
                            } else if (gridType == 'column') {
                                this.renderColumnGridHtml($parentEl, gridData);
                            }
                        }
                        
                    }
                }
            }
        },
        renderHeadGridHtml: function($parentEl, gridData) {
            var dateFormat = this.view.options.headerDateFormat;
            var classes = getSingleDayGridClasses(gridData);
            var html = '<div class="gic-col gic-day-col inline-col col-1-'+this.colCnt+' '+classes+'">' +
                        '<div class="gic-bg"></div>' +
                        '<div class="gic-content">'+gridData.format(dateFormat)+'</div>' +
                    '</div>';
            $parentEl.append(html);
	},
        renderColumnGridHtml : function ($parentEl, gridData) {
            var dateFormat = this.view.options.dateFormat;
            var html = '<div class="gic-row gic-day-row">' +
                    '<div class="gic-bg"></div>' +
                    '<div class="gic-content">'+gridData.format(dateFormat)+'</div>' +
                '</div>';
            $parentEl.append(html);
        },
        renderBodyRowHtml : function ($parentEl, row) {
            var html = '<tr class="gic-tr-row day-row" id="day-row-'+row+'"></tr>';
            $parentEl.append(html);
        },
        renderBodyGridHtml : function ($parentEl, gridData) {
            var dateFormat = this.view.options.gridDateFormat;
            var classes = getSingleDayGridClasses(gridData);
            var html = '<td class="gic-td-col day-col"><div class="gic-col gic-body-cell inline-col col-1-'+this.colCnt+' '+classes+'" id="id-cell-'+gridData.format('MM-D')+'">' +
                    '<div class="gic-bg"></div>' +
                    '<div class="gic-content"><span class="grid-date">'+gridData.format(dateFormat)+'</span><span class="-grid-events"></span></div>' +
                '</div></td>';
            $parentEl.append(html);
        },
        
//        // Generates the HTML that goes before the all-day cells
//        renderBgIntroHtml: function () {
//            var view = this.view;
//            return '' +
//                '<td class="fc-axis ' + view.calendar.theme.getClass('widgetContent') + '" ' + view.axisStyleAttr() + '>' +
//                '<span>' + // needed for matchCellWidths
//                view.getAllDayHtml() +
//                '</span>' +
//                '</td>';
//        },
//        // Generates the HTML that goes before all other types of cells.
//        // Affects content-skeleton, helper-skeleton, highlight-skeleton for both the time-grid and day-grid.
//        renderIntroHtml: function () {
//            var view = this.view;
//            return '<td class="fc-axis" ' + view.axisStyleAttr() + '></td>';
//        }
    };
    
    function WeekDayGrid(view) {
        var _this = this;
            _this.view = view;
        _this.init();
        return _this;
    }
    WeekDayGrid.prototype = {
        init : function () {
            this.dateProfile = this.view.dateProfile;
            this.start = this.dateProfile.start;
            this.end = this.start.clone().add(1, "week");
            this.updateGridTable();
        },
        // Populates dates
	updateGridTable: function() {
            var date = this.start.clone();
            var gridDataArray = [];
            var gridPerRow = 7;
            var rowCnt = 1;
            while (date.isBefore(this.end)) {
                gridDataArray.push(date.day());
                date.add(1, 'days');
            }
            this.gridDataArray = gridDataArray;
            this.rowCnt = rowCnt;
            this.colCnt = gridPerRow;
	},
        renderGrids : function ($parentEl, gridType) {
            var gridDataArray = this.gridDataArray;
            if (gridDataArray !== undefined) {
                var rowCnt = this.rowCnt;
                var colCnt = this.colCnt;
                var gridDataIndex = 0;
                var gridData;
                var row;
                var col;

                // trigger dayRender with each cell's element
                for (row = 0; row < rowCnt; row++) {
                    for (col = 0; col < colCnt; col++) {
                        gridData = gridDataArray[gridDataIndex++];
                        if (gridData !== undefined) {
                            if (gridType == 'header') {
                                this.renderHeadGridHtml($parentEl, gridData);
                            } else if (gridType == 'body') {
                                //this.renderBodyGridHtml($parentEl, gridData);
                            } else if (gridType == 'column') {
                                //this.renderColumnGridHtml($parentEl, gridData);
                            }
                        }
                        
                    }
                }
            }
        },
        renderHeadGridHtml: function($parentEl, gridData) {
            var dayName = dayNameArry[gridData];
            var classes = 'grid-' + dayName;
            var html = '<div class="gic-col gic-day-col inline-col col-1-'+this.colCnt+' '+classes+'">' +
                        '<div class="gic-bg"></div>' +
                        '<div class="gic-content">'+dayName+'</div>' +
                    '</div>';
            $parentEl.append(html);
	},
//        renderColumnGridHtml : function ($parentEl, gridData) {
//            var dateFormat = this.view.options.dateFormat;
//            var html = '<div class="gic-row gic-day-row">' +
//                    '<div class="gic-bg"></div>' +
//                    '<div class="gic-content">'+gridData.format(dateFormat)+'</div>' +
//                '</div>';
//            $parentEl.append(html);
//        },
//        renderBodyGridHtml : function ($parentEl, gridData) {
//            var dateFormat = this.view.options.dateFormat;
//            var html = '<div class="gic-col gic-body-col">' +
//                    '<div class="gic-bg"></div>' +
//                    '<div class="gic-content">'+gridData.format(dateFormat)+'</div>' +
//                '</div>';
//            $parentEl.append(html);
//        },
    };
    
    function GroupGrid(view) {
        var _this = this;
            _this.view = view;
        _this.init();
        return _this;
    }
    GroupGrid.prototype = {
        init : function () {
            this.updateGridTable();
        },
        // Populates resource
	updateGridTable: function() {
            //this.gridGridArray = ((this.gridGridArray)? this.gridGridArray:[]).concat(this.view.viewGroups);
            this.gridGridArray = this.view.viewGroups;
	},
        renderGrids : function ($parentEl, gridType) {
//console.log('renderGrids this.view.viewGroups');            
//console.log(this.view.viewGroups);

            var gridGridArray = this.gridGridArray;
            if (gridGridArray !== undefined) {
                var gridData;
                for (var i=0 ; i<gridGridArray.length; i++) {
                    gridData = gridGridArray[i];
                    if (gridData !== undefined) {
                        if (gridType == 'header') {
                            this.renderHeadGridHtml($parentEl, gridData);
                        } else if (gridType == 'body') {
                            this.renderBodyGridHtml($parentEl, gridData);
                        } else if (gridType == 'column') {
                            this.renderColumnGridHtml($parentEl, gridData);
                        }
                    }
                    
                }
            }
        },
        renderHeadGridHtml: function($parentEl, gridData) {
            //@todo
	},
        renderColumnGridHtml : function ($parentEl, gridData) {
            var id = gridData.id;
            var title = gridData.title;
            var html = '<tr class="gic-tr-col group-grid" id="group-grid-'+id+'"><td class="gic-td-col group-col">';
                    html += '<div class="gic-row gic-group-row">';
                        html += '<div class="gic-bg"></div>';
                        html += '<div class="gic-content">';
//            for (var key in gridData) {
//                html += '<span class="group-'+key+'">'+gridData[key]+'</span>';
//            }
            html += '<span class="group-title">'+title+'</span>';
            
            html += '</div></div></td></tr>';
            
            $parentEl.append(html);
        },
        renderBodyGridHtml : function ($parentEl, gridData) {
            //@todo
        },
//        clear : function ($parentEl) {
//            $parentEl.find('.group-grid').remove();
//            this.gridDataArray = [];
//        }
    };
    

    /**
    */
    $.fn.GICalendar = function (params) {
        if (params) params = $.extend(true, {}, defaults, params);
        var cal  = new GICalendar(this, params);
        return cal;
    }
     
})(window.jQuery || window.Zepto, window, document);
