   // For clickLog Player
   // need global variable 'clickLogs' = {timestamp: clickType, ...}
   var logs = [];
   var logs_time = [];
   var logs_play_id = -1;
   var clickLog_Playing = true;
   var general_delay = 1000;
   var click_show_css = {'box-shadow': '0px 0px 10px 3px #FF9900, 0px 0px 5px 1px #FF9900 inset', 'background-color': 'rgba(255, 153, 0, 0.3)'};
   var click_hide_css = {'box-shadow': '0px 0px 0px 0px white', 'background-color': 'rgba(0, 0, 0, 0)'};

   function setClickLog(_date){
      clickLog_Playing = true;
      logs_play_id = -1;
      logs = [];
      logs_time = [];
      for(var timestamp in clickLogs){
         if(timestamp.substr(0, 10) == _date){
            logs.push(clickLogs[timestamp]);
            logs_time.push(timestamp);
         }
      }
      $("#total_logs").text(logs.length);
      $("#cur_log").text('-');
   }

   function startPlayingClickLog(){
      clickLog_Playing = true;
      playClickLog();
   }

   function playClickLog(){
      playClickSeq(logs, ++logs_play_id);
   }

   function stepPlayingClickLog(){
      if(logs_play_id == logs.length - 1) return;
      clickLog_Playing = false;
      logs_play_id++;
      playClickSeq(logs, logs_play_id);
   }

   function stopPlayingClickLog(){
      clickLog_Playing = false;
   }

   function playClickSeq(click_seq, start_id, delay){
      if(click_seq.length == 0){
         $("#cur_log").text('No Date');
         return;
      }
      delay = typeof delay !== 'undefined' ? delay : general_delay;
      var i = typeof start_id !== 'undefined'? start_id : 0;
      var length = lengthOf(click_seq);
      function playClick(){
         logs_play_id = i;
         $("#cur_log").text((i+1) + ":" + logs_time[i] + ", " + click_seq[i]);
         var click_delay = clickFunctionMap(click_seq[i]);
         if(click_delay == -1) console.log("NOT IMPLEMENTED: " + click_seq[i]);
         else console.log("Play: " + click_seq[i]);
         i++;
         if(i < length && clickLog_Playing){
            if(click_delay == -1)
               playClick();
            else
               setTimeout(playClick, delay + click_delay);
         }
      }
      
      playClick();
   }

   // Mapping from click type to animation functions
   function clickFunctionMap(click){
      if(click.substr(0, 25) == 'STORYTELLING_CHART_BUTTON') click = 'STORYTELLING_CHART_BUTTON'; // elimitate extension code

      switch(click){
         case 'TAB_TEST':                          return changePage('test');
         case 'TAB_STATISTIC':                     return changePage('statistic');
         case 'TAB_STORYTELLING':                  return changePage('story');

         case 'TEST_TUTORIAL_BUTTON':              return TestTutorial();
         case 'TEST_START_BUTTON':                 return TestStart();
         case 'TEST_RESTART_BUTTON':               return TestRestart();

         case 'TEST_QUESTION_CANCEL':              return TestQuestion('cancel');
         case 'TEST_QUESTION_SEND':                return TestQuestion('send');
         case 'TEST_QUESTION_SEND_DATA':           return TestQuestion('send');

         case 'STATISTIC_TODAY_VIEW':              return changeStatisticRecord('day');
         case 'STATISTIC_WEEKLY_VIEW':             return changeStatisticRecord('week');
         case 'STATISTIC_MONTHLY_VIEW':            return changeStatisticRecord('month');
         case 'STATISTIC_ANALYSIS_TOUCH':          return StatisticTouch();
         case 'STATISTIC_QUESTION_BUTTON':         return StatisticQuestion('open');
         case 'STATISTIC_QUESTION_CANCEL':         return StatisticQuestion('cancel');
         case 'STATISTIC_QUESTION_SELECT':         return StatisticQuestion('select');
         case 'STATISTIC_QUESTION_NEXT':           return StatisticQuestion('next');

         case 'STORYTELLING_FLING_UP':             return flingStoryPage('up');
         case 'STORYTELLING_FLING_DOWN':           return flingStoryPage('down');
         case 'STORYTELLING_CHART_TYPE0':          return changeStoryTab('emotion');
         case 'STORYTELLING_CHART_TYPE1':          return changeStoryTab('desire');
         case 'STORYTELLING_CHART_TYPE2':          return changeStoryTab('detection');
         case 'STORYTELLING_CHART_TYPE3':          return changeStoryTab('all');
         case 'STORYTELLING_CHART_TOUCH':          return StoryTouch();
         case 'STORYTELLING_CHART_BUTTON':         return StoryButton();
         case 'STORYTELLING_RECORD_CANCEL':        return StoryRecord('cancel');
         case 'STORYTELLING_RECORD_RECORD':        return StoryRecord('record');
         case 'STORYTELLING_RECORD_CANCEL_RECORD': return StoryRecord('stop_record');
         case 'STORYTELLING_RECORD_PLAY':          return StoryRecord('play');
         case 'STORYTELLING_RECORD_CANCEL_PLAY':   return StoryRecord('stop_play')
        
         case 'EMOTIONDIY_RETURN_BUTTON':          return EmotionDIY('return');

         case 'EMOTIONMANAGE_RETURN_BUTTON':       return EmotionManage('return');

         case 'MENU_EMOTIONDIY':                   return Menu('emotionDIY');
         case 'MENU_EMOTIONMANAGE':                return Menu('emotionManage');
         case 'MENU_ABOUT':                        return Menu('about');
 
         default: return -1;
      }
   }

   // All animation functions

   // Click Animation
   function clickAnim(type, pos_size){
      var _click;
      switch(type){
         case 'rect':   _click = $("#rect_click"); break;
         case 'circle': _click = $("#circle_click"); break;
         default:       return;
      }

      if(pos_size == undefined) return;
      _click.css(pos_size);
      _click.transition(click_show_css).transition(click_show_css, 100, 'linear').transition(click_hide_css, 200, 'linear');
   }

   // Change Basic Page
   function changePage(type, click){
      if(!click) click = true;

      $("#emotionDIY_page").hide();
      $("#emotionManage_page").hide();
      $("#about_page").hide();
      switch(type){
         case 'test':
                      var click_pos = {'top': '430px', 'left': '315px', 'width': '90px', 'height': '50px'};
                      if(click) clickAnim('rect', click_pos);
                      $("#test_page").fadeIn('slow');
                      $("#statistic_page").fadeOut('slow');
                      $("#story_page").fadeOut('slow');
                      break;
         case 'statistic':
                      var click_pos = {'top': '430px', 'left': '405px', 'width': '90px', 'height': '50px'};
                      if(click) clickAnim('rect', click_pos);
                      $("#statistic_page").fadeIn('slow');
                      $("#story_page").fadeOut('slow');
                      break;
         case 'story':
                      var click_pos = {'top': '430px', 'left': '495px', 'width': '90px', 'height': '50px'};
                      if(click) clickAnim('rect', click_pos);
                      $("#story_page").fadeIn('slow');
                      break;
      }
      return 0;
   }

   // Test Page
   function TestTutorial(){
      $("#test_tutorial").fadeIn();
      setTimeout(function(){$("#test_tutorial").fadeOut()}, general_delay);
      return general_delay;
   }

   function TestStart(){
      $("#test_start").fadeIn();
      setTimeout(function(){$("#test_start").fadeOut()}, general_delay);
      return general_delay;
   }

   function TestRestart(){
      $("#test_restart").fadeIn();
      setTimeout(function(){$("#test_restart").fadeOut()}, general_delay);
      return general_delay;
   }

   function TestQuestion(type){
      $("#test_question").fadeIn('fast');
      var click_pos;
      switch(type){
         case 'cancel': click_pos = {'top': '350px', 'left': '350px', 'width': '100px', 'height': '40px'}; break;
         case 'send':   click_pos = {'top': '350px', 'left': '450px', 'width': '100px', 'height': '40px'}; break;
      }
      clickAnim('rect', click_pos);
      setTimeout(function(){$("#test_question").fadeOut()}, general_delay);
      return general_delay;      
   }

   // StoryTelling Page
   function changeStoryTab(type){
      switch(type){
         case 'emotion':
                         $(".story_tab").hide();
                         $("#story_emotion").fadeIn();
                         break;
         case 'desire':
                         $(".story_tab").hide();
                         $("#story_desire").fadeIn();
                         break;
         case 'detection':
                         $(".story_tab").hide();
                         $("#story_detection").fadeIn();
                         break;
         case 'all':
                         $(".story_tab").hide();
                         $("#story_all").fadeIn();
                         break;
      }
      return 0;
   }

   function flingStoryPage(direction){
      switch(direction){
         case 'up':
                      $("#Story_Paint").css({rotateX: '0deg', y: 285});
                      $("#Story_Paint").transition({rotateX: '-90deg', y: 143}, 1000, 'ease');
                      break;
         case 'down':
                      $("#Story_Paint").css({rotateX: '-90deg', y: 143});
                      $("#Story_Paint").transition({rotateX: '0deg', y: 285}, 1000, 'ease');
                      break;
      }
      return 0;
   }

   function StoryTouch(){
      $('#story_record').hide();
      $('#story_recording').hide();
      var click_pos = {'top': '318px', 'left': '315px', 'width': '270px', 'height': '109px'};
      clickAnim('rect', click_pos);
      return 0;
   }

   function StoryButton(){
      $("#story_chart_circle").fadeIn('fast');
      var click_pos = {'top': '320px', 'left': '400px', 'width': '30px', 'height': '30px'};
      setTimeout(function(){
         clickAnim('circle', click_pos); 
         setTimeout(function(){$("#story_record").fadeIn('slow'); $("#story_chart_circle").fadeOut('fast');}, general_delay);
      }, general_delay);
      return general_delay + general_delay;
   }

   function StoryRecord(type){
      if(type == 'cancel'){
         $("#story_recording").hide();
         var click_pos = {'top': '185px', 'left': '529px', 'width': '21px', 'height': '21px'};
         clickAnim('circle', click_pos);
         setTimeout(function(){$("#story_record").fadeOut();}, general_delay);
         return general_delay;
      }

      if(type == 'record'){
         var click_pos = {'top': '250px', 'left': '450px', 'width': '93px', 'height': '35px'};
         clickAnim('rect', click_pos);
         setTimeout(function(){$("#story_recording").fadeIn('slow');}, general_delay);
         return general_delay;
      }

      if(type == 'stop_record'){
         var click_pos = {'top': '250px', 'left': '450px', 'width': '93px', 'height': '35px'};
         clickAnim('rect', click_pos);
         setTimeout(function(){$("#story_recording").fadeOut('slow');}, general_delay);
         return general_delay;
      }

      if(type == 'play'){
         var click_pos = {'top': '250px', 'left': '355px', 'width': '93px', 'height': '35px'};
         clickAnim('rect', click_pos);
         setTimeout(function(){$("#story_recording").fadeIn('slow');}, general_delay);
         return general_delay;
      }

      if(type == 'stop_play'){
         var click_pos = {'top': '250px', 'left': '450px', 'width': '93px', 'height': '35px'};
         clickAnim('rect', click_pos);
         setTimeout(function(){$("#story_recording").fadeOut('slow');}, general_delay);
         return general_delay;
      }
   }

   // Statistic Page
   function changeStatisticRecord(type){
      changePage('statistic');
      switch(type){
         case 'day':
                      $("#Statistics_Record").animate({left: '315px'});
                      break;
         case 'week':
                      $("#Statistics_Record").animate({left: '45px'});
                      break;
         case 'month':
                      $("#Statistics_Record").animate({left: '-225px'});
                      break;
      }
      return 0;
   }

   function StatisticTouch(){
      $("#Questionnaire").hide();
      var click_pos = {'top': '212px', 'left': '315px', 'width': '270px', 'height': '215px'};
      clickAnim('rect', click_pos);
      return 0;
   }

   function StatisticQuestion(type){
      if(type == 'open'){
         $("#Questionnaire").fadeIn();
         return 0;
      }

      if(type == 'cancel'){
         var click_pos = {'top': '132px', 'left': '529px', 'width': '21px', 'height': '21px'};
         clickAnim('circle', click_pos);
         setTimeout(function(){$("#Questionnaire").fadeOut()}, general_delay);
         return general_delay;
      }

      var click_pos;
      switch(type){
         case 'select': click_pos = {'top': '200px', 'left': '360px', 'width': '174px', 'height': '40px'}; break;
         case 'next':   click_pos = {'top': '296px', 'left': '480px', 'width': '55px', 'height': '40px'}; break;
      }
      clickAnim('rect', click_pos);
      return 0;
   }

   // Menu
   function Menu(type){
      $("#menu").fadeIn('slow');
      var click_pos;
      switch(type){
         case 'emotionDIY':    click_pos = {'top': '370px', 'left': '356px', 'width': '188px', 'height': '37px'}; break;
         case 'emotionManage': click_pos = {'top': '407px', 'left': '356px', 'width': '188px', 'height': '37px'}; break;
         case 'about':         click_pos = {'top': '444px', 'left': '356px', 'width': '188px', 'height': '37px'}; break;
      }
      setTimeout(function(){
         clickAnim('rect', click_pos); 
         setTimeout(function(){
            switch(type){
               case 'emotionDIY':    $("#emotionDIY").fadeIn('slow'); break;
               case 'emotionManage': $("#emotionManage").fadeIn('slow'); break;
               case 'about':         $("#about").fadeIn('slow'); break;
            }
            $("#menu").fadeOut('slow');
         }, general_delay); 
      }, general_delay);
      return general_delay + general_delay;
   }

   // Emotion DIY
   function EmotionDIY(type){
      if(type == 'return'){
         $("#emotionDIY_page").fadeOut('slow');
         return 0;
      }
   }

   // Emotion Manage
   function EmotionManage(type){
      if(type == 'return'){
         $("#emotionManage_page").fadeOut('slow');
         return 0;
      }
   }

   // utility functions
   // fadeIn '_id', and hide all of other '_class' divs
   function tab_show(_class, _id){
      if($("#" + _id).length == 0) return;

      var shownIdx = $("." + _class).index($("." + _class + ":visible"));
      if(shownIdx == -1) {$("#" + _id).fadeIn('slow'); return;}
      
      var targetIdx = $("." + _class).index($("#" + _id));
      if(targetIdx == shownIdx) return;
      else if(targetIdx > shownIdx){
         $("#" + _id).fadeIn('slow', function(){
            $("." + _class + ":not(#" + _id + ")").hide();
         });
      }
      else{
         $("#" + _id).show();
         $("." + _class + ":not(#" + _id + ")").fadeOut('slow');
      }

   }
