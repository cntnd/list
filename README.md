# cntnd_list

To get started you have to run the [SQL statement located in `src/sql`](src/sql/cntnd_list.sql) within your contenido distribution.

## Elements

* `internal`: for javascript code
* `Title`: Headline `<h2>`
* `Text (singleline)`: Regular text within `<div>`
* `Text (multiline)`: Textara, with the option to use Markdown
* `Text (plain)`: Plain text without any HTML tags
* `Title for Link`: Title for a `<a>` link tag, with or without icon
* `Link or Download`: Dropdown where you can select medias, files or create an external or internal Link
* `URL`: Similar to `Link or Download` except you can enter only a valid URL
* `Image`: Displays several Images choosen from a dropdown, with or without comments (for fancybox)
* `Gallery (from folder)`: Displays a gallery choosen from a folder (several options how to display the gallery, with fancybox)

## CSS

Look at the [templates](src/templates/) for specific styles in there, an here is a list of generated css classes per element/tags.

### HTML Tags

* Links `<a>`: `cntnd_link`
* Images `<img>`: `cntnd_img`


### Elements

The style for the list elements are always added to the HTML Tag styles. And several generated HTML tags from an element includes as well the name of the list as css class. **To be sure, look at the generated HTML code.**

Example: Gallery element, listname: "example_list":
```html
<a href="#" class="example_list cntnd_link cntnd_gallery">
  <img src="src/to/image" class="example_list cntnd_img cntnd_gallery" />
</a>

```

* `internal`: none
* `Title` (`<h2>`): *listname*, `cntnd_title`
* `Text (singleline)/(multiline)` (`<div>`): `cntnd_text`
* `Text (plain)`: none
* `Title for Link` (`<span>`): *listname*, `cntnd_linktext`
* `Link or Download` (`<a>`): *listname*, *icon*, `cntnd_link`
  * *icon* styles are: (`pikto-after`), `pikto--word`, `pikto--excel`, `pikto--pdf`, `pikto--powerpoint`, `pikto--video`, `pikto--zip`, `pikto--link`, `pikto--link-intern`, `pikto--default`
* `Gallery (from folder)` (`<a>`, `<img>`): *listname*, `cntnd_gallery`
