# United Search

United Search extends Omeka-S with specialized search blocks that allow site visitors to find exactly what they're looking for within your collections. The module provides two powerful search blocks:

- **Item Set | Property Search**: Search within a specific item set by a selected property value
- **Dual Property Search**: Hierarchical property searching with dynamic filtering between related property values

The Dual Property Search is especially useful for working with hierarchical data like geographic information (states/counties) or organizational structures. When a user selects a value for the first property, the second property's options are dynamically filtered to show only related/relevant choices.

## Installation

To install this module:

1. Download the [latest release](https://github.com/Fisk-University/UnitedSearch/releases)
2. Unzip and rename the directory to "UnitedSearch"
3. Upload the directory to your Omeka S modules directory (usually at `/modules`)
4. Install from the Omeka-S admin → Modules menu

## Requirements

- Omeka S 4.0.0 or higher

## Configuration

This module does not require any site-wide configuration.

## Usage

### Adding a search block to a page

1. Add a page to your site or edit an existing page
2. Click "Add new block"
3. Select either "Item Set | Property Search" or "Dual Property Search"
4. Configure the search settings
5. Save the page

### Item Set | Property Search Configuration

- **Item Set**: Select the item set to search within
- **Property**: Choose the metadata property to search by
- **Placeholder**: Add custom placeholder text for the search input

### Dual Property Search Configuration

- **First Property**: Select the primary property (e.g., State)
- **Second Property**: Select the related secondary property (e.g., County)
- **Join Type**: Choose between:
 - "AND" - Shows only related values in the second dropdown (e.g., only counties in the selected state)
 - "OR" - Shows all possible values in the second dropdown

## Compatibility

This module has been tested with Omeka S 4.0.0.

## Technical Notes

United Search uses a combination of server-side data loading and client-side JavaScript to create the interactive search experience:

- For the Dual Property Search, the first property's values are loaded when the page is rendered
- Related values for the second property are pre-computed and stored in a relationship map
- When a value is selected in the first dropdown, JavaScript updates the second dropdown's options

The module works efficiently with large datasets by using optimized database queries to build the relationship maps.

## License

This module is published under the [Side Public License (SSPL-1.0)](https://www.mongodb.com/legal/licensing/server-side-public-license).
---

Built with ❤️ by LaTaevia Berry for Fisk University and HBCUs nationwide