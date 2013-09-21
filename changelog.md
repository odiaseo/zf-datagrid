#Change Log
##21/09/2013
### Bug fixes
  * Fixed typo in grid_rul_generator function call
  * Fixed bug in filtering subgrid data. Data returned was not filtered by fieldName.
  * Fixed pager info on the navigation grid for subgrids as grid
  * Fixed duplicate order by clauses on the same column when generating paginator
  * Fixed bug in columnResize cookie function
###Enhancements
  * set default width for subGrids
  * Modified setGridIndentity method to allow idSuffix to distinquish multiple grids generated from the same entity on displayed on the same page
  * Created class constants for SubGrid values
  * Refactored JS function to resize grid to fit container class

##03/09/2013 - v0.5.0
+ Major refactor of the module. APIs not affected.
+ Added ability to specify custom QueryBuilder

##30/08/2013
+ Added support for multiple subgrids (subgrid as grid)
+ Added caption to subgrid