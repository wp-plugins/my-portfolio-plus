$(function() 
{	
	$("#sitePreview").colorbox({
		width:"80%", 
		height:"80%", 
		iframe:true,
		onOpen:function(){ $("body").css("overflow", "hidden"); }, //disable body scroll
		onCleanup:function(){ $("body").css("overflow", "auto"); }, //enable body scroll
	});
});