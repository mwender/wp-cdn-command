![WP CDN Command](https://raw.githubusercontent.com/mwender/wp-cdn-command/main/bin/thumbnail.png)

# WP CDN Command

A custom WP CLI command to interact with your CDN using `s3cmd`. This tool allows you to quickly upload and download files between your WordPress `/wp-content/uploads` directory and a configured CDN bucket.

## Installation

1. Clone or download this repository.
2. Package the command for WP CLI:
   ```bash
   wp package install mwender/wp-cdn-command
   ```
3. Ensure `s3cmd` is installed and properly configured on your system.

## Usage

### Configuring the CDN Bucket

Before using the command, you need to configure your CDN bucket:
```bash
wp cdn configure
```
Follow the prompts to set the bucket name.

### Checking Configuration Status

Run a quick diagnostic to ensure your environment is properly configured:
```bash
wp cdn status
```

### Uploading Files to the CDN

Upload a folder from your local `/wp-content/uploads` directory to the configured CDN:
```bash
wp cdn put <folder>
```
Replace `<folder>` with the relative path of the folder inside `/wp-content/uploads`.

### Downloading Files from the CDN

Download a folder from the CDN to your local `/wp-content/uploads` directory:
```bash
wp cdn get <folder>
```
Replace `<folder>` with the relative path of the folder on the CDN.

## Changelog

### 1.0.0
- Initial release with `status`, `configure`, `put`, and `get` commands.

## License

This project is licensed under the MIT License. See LICENSE for more information.

