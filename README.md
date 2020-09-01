# WordPress Style Customizer
## Change the look of your entire site, no CSS required

The Style Customizer plugin enables theme developers to define attributes of an SCSS stylesheet that users can override
directly through the WordPres UI. Each time an update is made, the SCSS is recompiled on the fly and automatically versioned
to eliminate issues associated with cached assets.

To add Style Customizer to your theme, simply create a file at the root named style-customizer-config.json. Then, describe
the items that should be user configurable using JSON, as shown below.

```json
{
    "type": "scss", //Currently the only supported option but may be extended to others
    "entrypoints": {
        "scss/theme.scss": "css/theme.css" //Provides a mapping of each input files to its output.
    },
    "variables": [ //As many as you like
        {
            "name": "text-color", //The name of the variable as it appears in SCSS
            "category": "Colors", //Used for organizing the UI
            "type": "color", //Used for validation
            "title": "Primary text color",
            "description": "This color will be applied to all body text, exclusive of headers, links, etc.",
            "defaultValue": "#333",
            "order": 1 //Used for determining the order variables are output in the SCSS. This is primarily important
                       //if a user might want to refer to one variable in setting another. If that is the case,
                       //the variable being referenced must have a lower order.
        }
    ]
}
```
