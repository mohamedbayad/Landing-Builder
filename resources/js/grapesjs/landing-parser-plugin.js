/**
 * GrapesJS Landing Page Parser Plugin
 * 
 * Requirements:
 * 1. Parse HTML string -> Add <section> tags as Blocks.
 * 2. Define 'product-card' component with specific traits.
 */

export default function landingParserPlugin(editor, options = {}) {
    const { BlockManager, Components, Commands } = editor;

    // --- Part 1: Section to Block Converter ---

    // Command to parse HTML and create blocks
    // Usage: editor.runCommand('landing-page:parse', { html: '...' });
    Commands.add('landing-page:parse', (editor, sender, opts = {}) => {
        const htmlString = opts.html || '';
        if (!htmlString) {
            console.warn('No HTML string provided to landing-page:parse');
            return;
        }

        processHtmlToBlocks(editor, htmlString);
    });

    /**
     * Core logic to split HTML into blocks
     */
    function processHtmlToBlocks(editor, rawHtmlString) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(rawHtmlString, "text/html");

        // Target all direct children (sections, divs, etc.)
        const sections = Array.from(doc.body.children);

        sections.forEach((section, index) => {
            // 1. Determine Name & Category based on ID/Class
            let label = section.id || section.className || `Block ${index + 1}`;
            // Clean up label if it's too long or has multiple classes
            if (label.length > 20) label = label.substring(0, 20) + '...';

            let category = 'Custom Imported';
            const lowerLabel = (section.id + ' ' + section.className).toLowerCase();

            if (lowerLabel.includes('hero')) category = 'Hero';
            else if (lowerLabel.includes('footer')) category = 'Footer';
            else if (lowerLabel.includes('nav') || lowerLabel.includes('header')) category = 'Header';
            else if (lowerLabel.includes('price') || lowerLabel.includes('pricing')) category = 'Pricing';
            else if (lowerLabel.includes('feature')) category = 'Features';
            else if (lowerLabel.includes('testimonial')) category = 'Testimonials';
            else if (lowerLabel.includes('contact')) category = 'Contact';

            // 2. Add to GrapesJS
            // Ensure unique ID
            const blockId = `imported-block-${index}-${Date.now()}`;

            BlockManager.add(blockId, {
                label: label,
                category: category,
                content: section.outerHTML, // Actual HTML
                attributes: { class: 'fa fa-cube' } // Generic icon
            });
        });

        console.log(`Processed ${sections.length} blocks from HTML.`);
    }


    // --- Part 2: Custom "Product Pack" Component ---

    Components.addType('product-card', {
        isComponent: el => {
            // Detection Logic: class contains .product-card
            if (el && el.classList && el.classList.contains('product-card')) {
                return { type: 'product-card' };
            }
        },
        model: {
            defaults: {
                // Traits (Settings)
                traits: [
                    {
                        type: 'text',
                        name: 'data-product-id',
                        label: 'Product ID',
                    },
                    {
                        type: 'text',
                        name: 'data-product-title',
                        label: 'Product Title',
                    },
                    {
                        type: 'number',
                        name: 'data-product-price',
                        label: 'Price',
                    }
                ],
            },
        },
    });

    // --- Part 3: Custom "Product Button" Component ---

    Components.addType('product-button', {
        isComponent: el => {
            // Detection: class contains .btn-add-cart
            if (el && el.classList && el.classList.contains('btn-add-cart')) {
                return { type: 'product-button' };
            }
        },
        model: {
            defaults: {
                name: '🛒 Product Button', // Layer Name
                // Traits (Settings)
                traits: [
                    {
                        type: 'text',
                        name: 'data-product-label',
                        label: 'Label (ID)',
                    },
                    {
                        type: 'number',
                        name: 'data-price',
                        label: 'Price',
                    },
                    {
                        type: 'text',
                        name: 'data-title',
                        label: 'Title',
                    }
                ],
            },
        },
    });
}
