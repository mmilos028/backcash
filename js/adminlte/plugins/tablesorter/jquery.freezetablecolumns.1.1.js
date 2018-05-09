(function(e){function t(t,n){e(t).each(function(t,r){e(n).get(0).appendChild(r)})}function n(t){return e.extend({width:800,height:800,numFrozen:1,frozenWidth:-1,clearWidths:true},t||{})}e.fn.freezeTableColumns=function(r){r=n(r);var i=e(this);var s=i.attr("id");if(!s){console.log("jquery.freezetablecolumns.js: Error initializing frozen columns - source table must have a unique id attribute.");return}i.after('<div id="'+s+'-div" style="display: inline-block;"></div>');i.detach();main_div=e("#"+s+"-div");main_div.append('<div id="'+s+'-row1" style="white-space: nowrap;">'+'<div id="'+s+'-region1" style="display: inline-block; vertical-align: top; width: 5px; overflow: hidden;"><div></div></div>'+'<div id="'+s+'-region2" style="display: inline-block; vertical-align: top; width: 5px; overflow-x: hidden; overflow-y: scroll;"><div></div></div>'+"</div>");main_div.append('<div id="'+s+'-row2" style="white-space: nowrap;">'+'<div id="'+s+'-region3" style="display: inline-block; vertical-align: top; height: 100%; width: 5px; overflow-y: hidden; overflow-x: scroll;"><div></div></div>'+'<div id="'+s+'-region4" style="display: inline-block; vertical-align: top; height: 100%; width: 5px; overflow: scroll;"><div></div></div>'+"</div>");var o=i.get(0).cloneNode(false);e(o).removeAttr("id");t(o,e("#"+s+"-region1").children("div"));e("#"+s+"-region1").children("div").children("table").append("<thead></thead>");var u=e("#"+s+"-region1 > div > table > thead");i.children("thead").children("tr").each(function(n,i){u.append("<tr></tr>");var s=u.children("tr:last");var o=0;e(i).children("td,th").each(function(){if(o>=r.numFrozen){return false}t(this,s);if(typeof e(this).attr("colspan")==="undefined"){o+=1}else{o+=e(this).attr("colspan")}})});var a=i.get(0).cloneNode(false);e(a).removeAttr("id");t(a,e("#"+s+"-region2").children("div"));t(i.children("thead"),e("#"+s+"-region2").children("div").children("table"));var f=i.get(0).cloneNode(false);e(f).removeAttr("id");t(f,e("#"+s+"-region3").children("div"));e("#"+s+"-region3").children("div").children("table").append("<tbody></tbody>");var l=e("#"+s+"-region3 > div > table > tbody");i.children("tbody").children("tr").each(function(n,i){l.append("<tr></tr>");var s=l.children("tr:last");var o=0;e(i).children("td,th").each(function(){if(o>=r.numFrozen){return false}t(this,s);if(typeof e(this).attr("colspan")==="undefined"){o+=1}else{o+=e(this).attr("colspan")}})});t(i,e("#"+s+"-region4").children("div"));for(var c=1;c<=4;c++){e("#"+s+"-region"+c).children("div").children("table").css("table-layout","fixed")}i.freezeTableColumnsLayout(r);var h={"-region1":["-region3","-region2"],"-region2":["-region4","-region1"],"-region3":["-region1","-region4"],"-region4":["-region2","-region3"]};main_div.children("div").children("div").scroll(function(t){var n=e(this).attr("id").substr(e(this).attr("id").lastIndexOf("-"));e("#"+s+h[n][0]).scrollLeft(e(this).scrollLeft());e("#"+s+h[n][1]).scrollTop(e(this).scrollTop())})};e.fn.freezeTableColumnsLayout=function(t){function i(n,r){if(t.clearWidths){n.children("div").children("table").width("");r.children("div").children("table").width("");n.children("div").children("table").children("thead,tbody").children("tr").children("td,th").each(function(){e(this).removeAttr("width");e(this).css("width","")});r.children("div").children("table").children("thead,tbody").children("tr").children("td,th").each(function(){e(this).removeAttr("width");e(this).css("width","")})}var i=null;n.children("div").children("table").children("thead,tbody").children("tr").each(function(t){if(e(this).children("td,th").filter("[colspan]").length==0){i=e(this);return false}});var s=null;r.children("div").children("table").children("thead,tbody").children("tr").each(function(t){if(e(this).children("td,th").filter("[colspan]").length==0){s=e(this);return false}});if(i==null||s==null){return}n.children("div").children("table").children("colgroup").remove();n.children("div").children("table").prepend("<colgroup></colgroup>");r.children("div").children("table").children("colgroup").remove();r.children("div").children("table").prepend("<colgroup></colgroup>");i.children("td,th").each(function(t){var i=s.children("td,th").eq(t);var o=Math.max(e(this).width(),i.width())+10;n.children("div").children("table").children("colgroup").append('<col width="'+o+'"/>');r.children("div").children("table").children("colgroup").append('<col width="'+o+'"/>')})}function s(t,n){t.children("div").children("table").children("thead,tbody").children("tr").each(function(t){var r=n.children("div").children("table").children("tbody,thead").children("tr").eq(t);var i=Math.max(e(this).height(),r.height());e(this).height(i);r.height(i)})}t=n(t);var r=e(this).attr("id");i(e("#"+r+"-region1"),e("#"+r+"-region3"));i(e("#"+r+"-region2"),e("#"+r+"-region4"));s(e("#"+r+"-region1"),e("#"+r+"-region2"));s(e("#"+r+"-region3"),e("#"+r+"-region4"));var o=e("#"+r+"-div").children("#"+r+"-row1").outerHeight();e("#"+r+"-div").children("#"+r+"-row2").height(t.height-o);e("#"+r+"-region1").children("div").width(5e4);e("#"+r+"-region2").children("div").width(5e4);e("#"+r+"-region3").children("div").width(5e4);e("#"+r+"-region4").children("div").width(5e4);var u=Math.max(e("#"+r+"-region1").children("div").children("table").outerWidth(),e("#"+r+"-region3").children("div").children("table").outerWidth());var a=Math.max(e("#"+r+"-region1").children("div").children("table").outerWidth(),e("#"+r+"-region4").children("div").children("table").outerWidth());if(t.frozenWidth<0){t.frozenWidth=u}e("#"+r+"-region1").width(t.frozenWidth);e("#"+r+"-region1").children("div").width(u);e("#"+r+"-region2").width(t.width-t.frozenWidth);e("#"+r+"-region2").children("div").width(a);e("#"+r+"-region3").width(t.frozenWidth);e("#"+r+"-region3").children("div").width(u);e("#"+r+"-region4").width(t.width-t.frozenWidth);e("#"+r+"-region4").children("div").width(a)};})(jQuery)