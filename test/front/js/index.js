$('.footable').footable({
  calculateWidthOverride: function() {
    return { width: $(window).width() };
  }
}); 

// See:
// http://www.sitepoint.com/responsive-data-tables-comprehensive-list-solutions