SORTABLEVIEWS

This is an alternative for the popular DraggableViews module.
The difference lies in that this module stores weights directly on
entity fields.

Here is how it works:

1. Create a view of any entity and have its format be any of
"Sortable HTML list", "Sortable Unformatted list" or
"Sortable table".

2. Make sure the entity type has a spare integer field or
base field to store the weight and specify it in the view format
settings.

3. Add your weight field as a sort criteria as well.

4. Finally, add the "Save Sortableviews changes" handler to either
your view header of footer.

5. Your view should now be sortable.

Be aware that the sorting process will always overwrite whatever
weight an entity had. Also, weight conflicts may occur if using
multiple sortableviews for the same entity type and bundle.
