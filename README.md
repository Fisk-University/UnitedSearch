# United Search

A specialized search extension for Omeka S that enables site visitors to find exactly what they are looking for within collections through advanced filtering and hierarchical search capabilities.

## Features

United Search provides two powerful search block options:

### Item Set | Property Search
Enables searching within specific item sets by filtering on property values. Users can narrow results to a single item set and search by specific metadata properties.

### Dual Property Search
Offers hierarchical searching with dynamic filtering between related properties. When users select values in the first dropdown, JavaScript dynamically updates the second dropdown's available options to show only relevant values, creating an intuitive search experience.

## Requirements

- Omeka S 4.0.0 or higher
- PHP 8.0 or higher
- JavaScript enabled in browser (for dynamic property filtering)

## Installation

1. Download the latest release from the GitHub releases page
2. Unzip the archive
3. Rename the directory to "UnitedSearch"
4. Upload the folder to your Omeka S `modules` directory
5. Log in to your Omeka S admin panel
6. Navigate to Modules and activate "United Search"

## Configuration

### Site-Wide Setup

United Search does not require site-wide configuration. Search functionality is added through the site page builder using search blocks.

### Using Search Blocks

1. Edit or create a page in Omeka S
2. Add a new block and select either "Item Set | Property Search" or "Dual Property Search"
3. Configure the block settings:
   - **Item Set | Property Search**: Select target item set and property to search
   - **Dual Property Search**: Configure the hierarchical property relationships
4. Save the page

The search blocks will appear on your site page and be immediately functional.

## Usage

### For Site Visitors

Users interact with search blocks through their browser interface:
- Select values from dropdown menus to narrow search results
- Results update dynamically as selections change
- Combine multiple filters to refine searches
- Clear selections to reset search

### For Site Administrators

The module integrates seamlessly with Omeka S page blocks. No special administration interface is required beyond standard Omeka S page management.

## Technical Implementation

United Search combines server-side data loading with client-side JavaScript to create responsive search experiences:
- Server-side: Database queries retrieve relevant values and item data
- Client-side: JavaScript updates dropdown options dynamically to reflect current selections
- Results: Immediate feedback as users modify search criteria

## Troubleshooting

### Search Block Not Appearing

- Verify the module is activated in Omeka S admin panel
- Ensure the page has been saved after adding the block
- Check that JavaScript is enabled in your browser
- Clear browser cache if seeing outdated content

### Dynamic Filtering Not Working

- Verify that the properties selected exist in your items
- Ensure items have values populated for the selected properties
- Check browser console (F12) for any JavaScript errors
- Try a different property combination

### Slow Search Performance

- Consider limiting search to smaller item sets if searching large collections
- Verify your server meets recommended specifications
- Check database query performance using Omeka S logs

## Support and Issue Reporting

For questions, bug reports, or feature requests:
- Visit the GitHub repository: https://github.com/Fisk-University/UnitedSearch
- Open an issue on the GitHub Issues page: https://github.com/Fisk-University/UnitedSearch/issues
- Include details about your Omeka S version, server environment, and steps to reproduce any issues

## Contributing

We welcome contributions from the digital humanities community. To contribute:

1. Fork the repository on GitHub
2. Create a feature branch for your changes
3. Make your modifications following the existing code style
4. Test thoroughly in a local Omeka S environment
5. Submit a pull request with a clear description of your changes

Contributors should follow Omeka S coding standards and ensure all changes are compatible with Omeka S 4.0.0 and higher.

## Development Setup

For developers interested in extending or modifying United Search:

1. Clone the repository: `git clone https://github.com/Fisk-University/UnitedSearch.git`
2. Install in an Omeka S instance at `modules/UnitedSearch`
3. The module structure follows standard Omeka S conventions:
   - `config/module.ini` - Module metadata
   - `src/` - PHP source code
   - `view/` - Template files
4. Review the existing block implementations in `src/View/Helper/` for examples
5. Test changes in a local Omeka S development environment

## Known Limitations

- Dynamic property filtering requires JavaScript to be enabled in users' browsers
- Search is limited to properties explicitly configured in the block settings
- Performance may degrade when searching very large item sets (10,000+ items)
- Only works with Omeka S 4.0.0 and higher

## Version History

### Version 1.0.0
- Initial release
- Item Set | Property Search block
- Dual Property Search block with dynamic filtering
- Basic support for Omeka S 4.0.0

See GitHub releases page for detailed changelogs: https://github.com/Fisk-University/UnitedSearch/releases

---

## License

This module is published under the [Server Side Public License (SSPL-1.0)](https://www.mongodb.com/legal/licensing/server-side-public-license).

---

## Authors

Developed by [LaTaevia Berry](https://github.com/LATAEVIA) and Sai Kiran Boppana for Fisk University and HBCUs nationwide.



## Project Context

United Search is part of the larger Rosenwald Fund Collection project, a Mellon Foundation-funded digital humanities initiative that provides archival infrastructure for institutions managing significant cultural heritage collections.

For more information about the project: https://github.com/Fisk-University/Rosenwald-Fund-Collection
