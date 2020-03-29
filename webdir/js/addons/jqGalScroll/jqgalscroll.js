/**
 * jQuery jqGalScroll Plugin
 * Examples and documentation at: http://benjaminsterling.com/2007/08/24/jquery-jqgalscroll-photo-gallery/
 *
 * @author: Benjamin Sterling
 * @version: 2.0
 * @copyright (c) 2007 Benjamin Sterling, KenzoMedia
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *   
 * @requires jQuery v1.2.1 or later
 * @optional jQuery Easing v1.2
 *
 * @name jqGalScroll
 * @example $('ul').jqGalScroll();
 * 
 * @Semantic requirements:
 * 				The structure fairly simple; the structure should consist
 * 				of a ul > li > img structure.
 * 
 * 	<ul>
 *		<li><img src="common/img/dsc_0003.thumbnail.JPG"/></li>
 *		<li><img src="common/img/dsc_0012.thumbnail.JPG"/></li>
 *	</ul>
 *
 * @param String ease
 *					refer to http://gsgd.co.uk/sandbox/jquery.easing.php for values
 * 
 * @example $('#gallery').jqGalScroll({speed:1000});
 
 * @param String speed
 * 					fast, slow, 1000, ext..
 * 
 * @example $('#gallery').jqGalScroll({speed:1000});
 * 
 * @param String height
 * 					the default height of your wrapper
 * 
 * @example $('#gallery').jqGalScroll({height:490});
 * 
 * @param String titleOpacity
 * 					the opacity of your title bar (if present)
 * 
 * @example $('#gallery').jqGalScroll({titleOpacity:.70});
 * 
 * changes:
 * 		10/05/2007
 * 			Removed: 	the up and down arrows, were not a useful as 
 * 							originally thought
 * 			Removed: 	the param for the nacArrowOpacity
 * 			Added:		some wrappers to allow for better styling.
 * 			Changed:	marginTop animation to just top
 *			Added:	When an image is too big or too small, it will
 *						align the img into the center.
 * 			
 */
(function($) {
	$.fn.jqGalScroll = function(options){
		return this.each(function(i){
			var el = this, $this = $(this).css({position:'relative'}), $children = $this.children();
			el.opts = $.extend({}, $.jqGalScroll, options);
			$this.css({height:el.opts.height,width:el.opts.width})
			el.index = i;
			el.container = $('<div id="jqGS'+i+'" class="jqGSContainer">').css({position:'relative'});
			el.ImgContainer = $('<div class="jqGSImgContainer" style="height:'+el.opts.height+'px;position:relative;overflow:hidden;">');
			$this.wrap(el.container);
			$this.wrap(el.ImgContainer);
			el.pagination = $('<div class="jqGSPagination">');
			$this.parent().parent().append(el.pagination);

			$children.each(function(){
				var $child = $(this).css({height:el.opts.height,width:el.opts.width});
				var $img = $child.children('img');
				var $loader = $('<div class="jqGSLoader">').appendTo($child);
				var $titleHolder = $('<div class="jqGSTitle">').appendTo($child).css({opacity:el.opts.titleOpacity}).hide();
				var image = new Image();
				$img.hide()
				image.onload = function(){
					image.onload = null;
					$loader.fadeOut();
					$img.css({marginLeft:-image.width*.5,marginTop:-image.height*.5}).fadeIn();
					var alt = $img.attr('alt');
					if(typeof alt != 'undefined'){
						$titleHolder.text(alt).fadeIn();
					}
				};
				image.src = $img.attr('src');
			});
			
			var $ul = $('<ul>');
			
			for(var i = 0; i < $children.size(); i++){
				var selected = '';
				if(i == 0) selected = 'selected';
				
				var $a = $('<a href="#'+(i)+'" class="'+selected+'">'+(i+1)+'</a>').click(function(){
					var href = this.href.replace(/^.*#/, '');
					el.pagination.filter('.selected').removeClass('selected');
					$(this).addClass('selected');
					$this.stop().animate({top:-($children.height()*href)},el.opts.speed, el.opts.ease);
					index = href;
					return false;
				});
				$('<li>').appendTo($ul).append($a);
			};
			el.pagination.append($ul);
		}); // end : this.each(function(){
	};
	$.jqGalScroll = {
		ease: null,
		speed:0,
		height: 'auto',
		width: 'auto',
		titleOpacity : .60
	};
})(jQuery);
