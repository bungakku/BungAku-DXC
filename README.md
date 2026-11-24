<p align="center">
  <img src="assets/bungaku-logo.png" alt="BungAku — Build • Manage • Grow" width="600">
</p>

# BungAku-DXC — DocX Converter

> **Rebranded.** BungAku-DXC was formerly **MEC Docx Converter**. It was renamed in **v1.2.0** as part of the BungAku brand. The repository moved from `bungakku/Mec-Docx-Converter` to `bungakku/BungAku-DXC` (old URLs redirect).

BungAku-DXC is a WordPress plugin that converts `.docx` documents, such as those created by Microsoft Word, Google Docs and LibreOffice, to clean HTML. It uses the semantic information in the document and ignores the rest. For example, a paragraph with the style Heading 1 becomes an `<h1>` element instead of an attempt to copy its font, size and colour. You can paste from Word documents without the usual mess.

## Upgrading from MEC Docx Converter

v1.2.0 is a clean break. The plugin folder and main file are now `bungaku-dxc` / `bungaku-dxc.php`, so WordPress treats it as a new plugin.

1. Deactivate and delete the old **MEC DOCX CONVERTER** plugin.
2. Install `bungaku-dxc-<version>.zip` from the [Releases](https://github.com/bungakku/BungAku-DXC/releases) page.
3. If you defined a custom `MEC_OPTIONS` function, rename it to `BUNGAKU_DXC_OPTIONS`. The old name is no longer read.

No conversion behaviour changed in the rename.

## Installation

1. Download `bungaku-dxc-<version>.zip` from the [Releases](https://github.com/bungakku/BungAku-DXC/releases) page.
2. In WordPress, go to **Plugins → Add New → Upload Plugin**, upload the zip and activate it.
3. When editing a post or page, find the **BungAku-DXC** meta box below the editor. If it is missing, enable it under **Screen Options**.

## Usage

1. Select a `.docx` file in the BungAku-DXC meta box.
2. Check the conversion in the **Visual** and **Raw HTML** tabs.
3. Click **Insert into editor**.

## Configuration

Advanced users can define a global JavaScript function `BUNGAKU_DXC_OPTIONS` to pass custom options to the bundled conversion library. It receives the library object as its argument. See `readme.txt` for details.

## Updates

The plugin checks the [GitHub releases](https://github.com/bungakku/BungAku-DXC/releases) of this repository for new versions.

## Support

Please use the [issue tracker](https://github.com/bungakku/BungAku-DXC/issues).

## Author

Biswajit Thokchom · [github.com/bungakku](https://github.com/bungakku)
