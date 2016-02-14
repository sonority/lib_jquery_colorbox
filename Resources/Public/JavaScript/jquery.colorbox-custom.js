jQuery(document).ready(function () {
	jQuery(".lightbox").colorbox({
		opacity: false,
		rel: function () {
			return $(this).attr("rel");
		},
		href: function () {
			return $(this).attr("href");
		},
		maxWidth: function () {
			if ($(window).width() < 768) {
				return "98%";
			} else {
				return "80%";
			}
		},
		maxHeight: function () {
			if ($(window).height() < 768) {
				return "98%";
			} else {
				return "80%";
			}
		},
		title: function () {
			return $(this).children("img").attr("title");
		},
		reposition: true
	});
	jQuery(".lightbox-video").colorbox({
		width: "1138px",
		height: "686px",
		iframe: true
	});
});
