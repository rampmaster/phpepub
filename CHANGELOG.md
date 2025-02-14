CHANGELOG
=========

Rev. 4.0.7 - 2016-03-16
---
* Fix: Error when image link has no extension in its file name.
* 

Rev. 4.0.6 - 2016-03-08
---
* Added: iBooksHelper
* Added: CalibreHelper
* Added: preliminary work for "Rendition" meta data
* Added: dir attributes to the epub3toc (if writing direction is rtl)
* Fixed: referenced links (url#id) broke on split chapters.
* Fixed: Problem where chapter index name in the metadata could have the first chapter repeated.
* Changed: Generated TOC file changed from using hardcoded spaces to indent nested chapters, to using the CSS, defaulting to 2em per level. the tocCss can override this by defining .level[1-n], though the default only defines indents for levels 1-7. reference links has their class as class=".level1 reference"
* Updated: J. King's DrUUID library

Rev. 4.0.5 - 2015-11-02
---
* Added: Support for SVG images. (experimental)
* Cleaned up source headers
* Added preliminary work for supporting Apple iBooks idiosyncrasies. If I could, I'd tell Apple to take a flying f*** off of a tall building for their tireless efforts to break established standards.

Rev. 4.0.4 - 2015-09-15
---
* Added: "DANGERMODE" to allow the user to get and manipulate the internal structure should they need it.
* Note, IF you really need to do any of this, let me know, in case it is really something the package itself should do.

Rev. 4.0.3 - 2015-09-15
---
* Changed: Added functions addCustomNamespace, addCustomPrefix, addCustomMetaValue and addCustomMetaProperty to enable users to add needed namespaces or prefixes (prefixes are only supported in ePub3) as well as meta properties and entries specified by those namespaces. See tests/EPub.Example1.php (line 95+) and tests/EPub.Example3.php (line 66+) for how they are used.
---------------------------------------------------------------------
Rev. 4.0.2 - 2015-09-15
* Changed: Added function addFileToMETAINF to enable users to add the apple ibooks metadata.
*   $book->addFileToMETAINF("com.apple.ibooks.display-options.xml", $xmldata);
---------------------------------------------------------------------
Rev. 4.0.1 - 2015-06-29
* Changed: Included J. King's DrUUID library rather than using composer. The only available package remained in "dev" stability.
*          http://jkingweb.ca/code/php/lib.uuid/
---------------------------------------------------------------------
Rev. 4.0.0 - 2015-04-29
* Added: Support for resizing animated gifs for use in ePub3 books.
* Added: Function to remove HTML comments.
* Changed: Now using composer. Structure and layout changed accordingly.
---------------------------------------------------------------------
Rev. 3.20 - 2014-01-01
* Fixed: Issue #15, where name space declarations were erroneously stripped off the html tag of added chapters.
* Fixed: An issue, where PNG images exceeding the maximum specified sizes were broken during resizing.
* Fixed: Issue #16, where ePub 3 multimedia needed to be added to the automatic chapter processing.
* Fixed: Potential issue related to Issue #16 with loading large files from external sources, where these might result in memory errors. These will now be loaded into a temp file on the server, before being added to the book.
* Fixed: Issue #17, where a function was called as a global.
---------------------------------------------------------------------
Rev. 3.10 - 2013-12-01
* Changes to TOC generation and references handling for improved results in specifically ePub 3
* Added better support for RTL writing direction in generated pages (TOC)
* Fixed: Author was not correctly added to the book metadata.
* Code clean up and comment documentation
---------------------------------------------------------------------
Rev. 3.00 - 2013-11-03
---------------------------------------------------------------------
* Added: ePub 3 support. The ePub 3 still contains the NCX for backwards compatibility to ePub 2 readers, though there is no gurantee that it'll work on all readers.
*  The EPub class constructor now takes arguments for epub version, EPub::BOOK_VERSION_EPUB2 or EPub::BOOK_VERSION_EPUB3
*  The other new parameters on the EPub constructor are for default language and writing direction, used on the generated ePub 3 TOC.
* Added: Ability to reference sub chapter id's
*  ->addChapter($chapterName, $chapterLink); where the link must contain the #id.
---------------------------------------------------------------------
Rev. 2.53 - 2013-10-05
* Added: Support for Chapter levels. 
* Added functions:
*  ->subLevel() to indent one level under the current, additional chapters are added under that.
*  ->backLevel() to step one level back to the parent of the current level.
*  ->rootLevel() to step back to the root of the navMap.
*  ->getCurrentLevel() to get the current level indentation (root = 1).
*  ->setCurrentLevel(int) to set the current level indentation (1 or less returns to the root, same as ->rootLevel()).
* ->buildTOC now reflects this level indentation if present.
* ePub250 is otherwise compatible to the previous version, and no modifications are needed if you don't need this feature.
* Added: Support for custom meta data via the function ->addCustomMetadata($name, $content);
*  It is the responsibility of the builder to ensure no invalid values are inserted.
*  Example use is for for instance Calibre, see EPub.Example2.php for use.
* Added: Support or user handled DublinCore meta data via the function ->addDublinCoreMetadata(DublinCore::constants, $content);
---------------------------------------------------------------------
Rev. 2.14/2.52 - 2013-10-05
* Change: Using EPub::EXTERNAL_REF_REPLACE_IMAGES as externalReferences parameter when adding chapters containing image links, will now cause EPub to use the alt attribute of the image, if it exists.
---------------------------------------------------------------------
Rev. 2.13 - 2013-09-26
* Fixed: sendBook and saveBook returned boolean TRUE/FALSE depending on if the sending was successful. The sanitized/used filename was never exposed. The methods now return the filename used for sending, or FALSE if it failed.
* Change: sanitizeFileName is now a public function.
---------------------------------------------------------------------
Rev. 2.12 - 2013-03-10
* Fixed: Decoding of html entities if added to the book metadata. Requires added file EPub.HtmlEntities.php
---------------------------------------------------------------------
Rev. 2.11 - 2013-02-24
* Fixed: image mimetype detection failed if EXIF was not installed on the PHP server.
---------------------------------------------------------------------
Rev. 2.10 - 2013-02-23
* Fixed: Undefined variable: isFileGetContentsInstalled
---------------------------------------------------------------------
Rev. 2.09 - 2013-02-17
* Fixed: Github issue #9 Corrupt ePub file generated when image url's contains extra parameters
* Fixed: Unable to load external images on some PHP installations due to restrictions on file_get_contents (PHP_INI: "allow_url_fopen" = false)
* Fixed: determining image mime type not working when external files could not be opened (see above restriction)
* Added: getFileContent to better handle external files
---------------------------------------------------------------------
Rev. 2.08 - 2012-09-03
* Fixed: PHPClasses Issue "Undefined index": Missing a check on the presence of a server variable.
---------------------------------------------------------------------
Rev. 2.07 - 2012-08-12
* Fixed: Github issue #5: I can't encode chinese
---------------------------------------------------------------------
Rev. 2.06 - 2011-06-06
* Changed: Adding chapters using an array of chapter parts now adds the part counter to the filename before the extension.
---------------------------------------------------------------------
Rev. 2.05 - 2011-06-03
* Updated: Zip to version 1.33, fixing a problem with empty sub directories

* Fixed: Problem where EPUBChecker would report a problem with the mimetype file having extra field data.

* Fixed: typo in a few variables, thanks to riconeitzel.
---------------------------------------------------------------------
Rev. 2.04 - 2011-03-13
* Fixed: The relPath function in previous versions had a bug where paths with elements containing non alphanumeric characters were not handled correctly. Function has now been rewritten and moved to the Zip class.

* Changed: function relPath is now deprecated, please use Zip::getRelativePath($relPath) instead.

* Added: Version check for the used Zip class.
---------------------------------------------------------------------
Rev. 2.03 - 2011-03-05
* Fixed: Cover was not showing on Stanza, the Cover image had not been referenced correctly in the book meta data.

* Added: Support of ePub meta tags Subject, Relation and Coverage.

* Added: Descriptions for ePub metadata tags, taken from the specification at http://dublincore.org/2010/10/11/dcelements.rdf#
---------------------------------------------------------------------
Rev. 2.02 - 2011-02-23
* Fixed: Failed in PHP 5.2 due to the way arrays were queries about the existence of a key.

* Addded const VERSION and REQ_ZIP_VERSION for future use to enable version check on dependencies.
---------------------------------------------------------------------
Rev. 2.01 - 2011-02-20
* Fixed: Sending failed when the Output buffer had been initialized with ob_start, but was empty.

* Changed: setIgnoreEmptyBuffer deprecated, function is now a default feature in Zip. (Zip.php v. 1.21)
---------------------------------------------------------------------
Rev. 2.00 - 2011-02-19
EPub Class:
* Important: Requires Zip.php version 1.2 or newer from http://www.phpclasses.org/package/6110

* Fix: EPub was loading the entire generated book into memory when finalized, it will now remain in the temp file if such have been used (typically for Books over 1 MB in size)

* Added: Constants for Identifier types: IDENTIFIER_UUID, IDENTIFIER_URI and IDENTIFIER_ISBN

* Added: Constants for External reference handling in addChapter and AddCSSFile: EXTERNAL_REF_IGNORE, EXTERNAL_REF_ADD, EXTERNAL_REF_REMOVE_IMAGES and EXTERNAL_REF_REPLACE_IMAGES.

* Added: Function setCoverImage(image path) to add a cover image to the book. Only one cover image can be added, and it will also create a XHTML entry called CoverPage.html

* Added: Protected function processChapterExternalReferences to process a HTML file and add referenced images and links, such as CSS files, and rewrite links to point to these inside the book. The function will not add the rewritten HTML, but will return it to the calling function as a DOMDocument or String depending on the input value.

* Added: Protected function processCSSExternalReferences to process a CSS file and add referenced images to the book, and rewrite these url's to point to these inside the book. The function does not add the rewritten CSS, but will return it as a string to the calling function.

* Changed: Function addChapter to include additional parameters $externalReferences (default EPub::EXTERNAL_REF_IGNORE) and $baseDir, these will cause the function to call processChapterExternalReferences before adding the html file.

* Changed: Function addCSSFile to include additional parameters $externalReferences (default EPub::EXTERNAL_REF_IGNORE) and $baseDir, these will cause the function to call processCSSExternalReferences before adding the CSS file.

* Added: Function getFileList() to get an array with the files added to the book, key will be the file path and name in the archive, and the value is the corrosponding path added, almost always identical, except in files added via the addChapterExternalReferences and addCSSExternalReferences functions.

* Added: Boolean return values to most functions to signal if the function succeeded. Others will return their normal value if successful, and false if it failed, such as when the book has been finalized.

* Added: "Getter" functions for most parameters to which there were "setter" functions. It is done deliberately as very few values can be modified directly without breaking the generated book, in the worst case resulting in an invalid or corrupt archive.

* Fix: The old UUID generation were faulty, and EPub now uses J. King's (http://jkingweb.ca/) DrUUID class for UUID generation as it is RFC4122 compliant. This WILL require an update of code which were using the old createUUID function, as the arguments have changed. See documentation for the method inside the EPub class.

* Fix: If no Identifier have been set before finalize, a random UUID will be generated.

* Fix: If no SourceURL have been set before finalize, the current URL is used.

* Fix: If no Publisher URL have been set before finalize, the current Server address is used.

* Added: Function getBookSize() to return the size of the generated book.

* Changed: Function sendBook, will automatically append .epub to the filename if this is missing.

* Added: Function getImage(filename), which will retrieve the image, determine it's size and mime type and return thie information in an array with the keys "width", "height", "mime" and "image". If GD is available the image will be resized to the limits set by the $maxImageWidth and $maxImageHeight variables in the EPub class if it exceeds these limits. Aspect ratio will be retained.

* Added: Utility functions  getCurrentPageURL() and getCurrentServerURL()

* Added setSplitSize and getSplitSize for setting the books autoSplit target size.

EPubChapterSplitter Class:
* Added: Split HTML files by search string, for instance chapters. Note, chapter search disables size checks. Returned chapter have the matched line from the HTML returned as the key of the returned array. This can be used to generate the correct chapter name entry in the book, see the updated EPub.Example1.php.
---------------------------------------------------------------------
Rev. 1.00
Initial release
