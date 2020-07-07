/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Options } from '../components/options-context-provider';
import { ReaderThemeSelection } from '../components/reader-theme-selection';

export function ReaderThemes() {
	const { editedOptions } = useContext( Options );

	const { theme_support: themeSupport } = editedOptions;

	if ( 'reader' !== themeSupport ) {
		return null;
	}

	return (
		<div className="reader-themes">
			<h2>
				{ __( 'Choose Reader Theme', 'amp' ) }
			</h2>
			<ReaderThemeSelection />
		</div>
	);
}