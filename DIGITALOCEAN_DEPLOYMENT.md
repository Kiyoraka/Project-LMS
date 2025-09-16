# DigitalOcean Deployment Guide - File Storage Solution

## Problem
When deploying to DigitalOcean App Platform, uploaded files (images, videos) are lost on each deployment because the local storage is not persistent. The container is rebuilt on each deployment, losing all locally stored files.

## Solution: Use DigitalOcean Spaces

### Step 1: Create a DigitalOcean Space

1. Go to DigitalOcean Dashboard
2. Click "Create" → "Spaces"
3. Choose a datacenter region (e.g., NYC3)
4. Give your Space a name (e.g., `lms-storage`)
5. Select "Public" for file access
6. Create the Space

### Step 2: Generate Spaces Access Keys

1. Go to API → Tokens/Keys in DigitalOcean
2. Under "Spaces access keys", click "Generate New Key"
3. Name it (e.g., "LMS App")
4. Save the Access Key and Secret Key

### Step 3: Configure Your App

In DigitalOcean App Platform, add these environment variables:

```bash
# Change filesystem disk to spaces for production
FILESYSTEM_DISK=spaces

# DigitalOcean Spaces Configuration
DO_SPACES_KEY=your_access_key_here
DO_SPACES_SECRET=your_secret_key_here
DO_SPACES_REGION=nyc3
DO_SPACES_BUCKET=your-space-name
DO_SPACES_ENDPOINT=https://nyc3.digitaloceanspaces.com
DO_SPACES_URL=https://your-space-name.nyc3.digitaloceanspaces.com
```

### Step 4: Install Required Package

Make sure the AWS S3 package is installed (required for Spaces):

```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

### Step 5: Update Your Code

The application is already configured to handle both local and Spaces storage. The key files are:

- `config/filesystems.php` - Contains Spaces configuration
- `app/Helpers/FileStorageHelper.php` - Handles storage based on environment
- `app/Helpers/StorageHelper.php` - Handles URL generation

### Step 6: Migrate Existing Files (if any)

If you have existing files in production, you'll need to migrate them to Spaces:

1. Download existing files from the app
2. Upload them to your Space maintaining the same directory structure
3. Update database paths if necessary

### Step 7: Set CORS Policy for Spaces (Important!)

1. Go to your Space settings
2. Click on "Settings" → "CORS"
3. Add this configuration:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<CORSConfiguration>
    <CORSRule>
        <AllowedOrigin>*</AllowedOrigin>
        <AllowedMethod>GET</AllowedMethod>
        <AllowedMethod>PUT</AllowedMethod>
        <AllowedMethod>POST</AllowedMethod>
        <AllowedMethod>DELETE</AllowedMethod>
        <MaxAgeSeconds>3000</MaxAgeSeconds>
        <AllowedHeader>*</AllowedHeader>
    </CORSRule>
</CORSConfiguration>
```

Or for more security, replace `*` in AllowedOrigin with your app URL:
```xml
<AllowedOrigin>https://lmsprototype-app-yo8va.ondigitalocean.app</AllowedOrigin>
```

## Alternative: Use Volumes (Not Recommended for App Platform)

DigitalOcean App Platform doesn't support persistent volumes. If you need local storage, consider:
1. Migrating to a Droplet with Docker
2. Using DigitalOcean Kubernetes
3. Using a traditional VPS setup

## Testing

After configuration:
1. Upload a test image/video through your app
2. Check if it appears in your Space
3. Deploy a new version of your app
4. Verify the files are still accessible

## Benefits of Using Spaces

- ✅ Files persist across deployments
- ✅ CDN-enabled for fast delivery
- ✅ Scalable storage
- ✅ Backup options available
- ✅ Cost-effective for media storage

## Troubleshooting

### Files not uploading to Spaces
- Check your access keys are correct
- Verify the bucket name and region
- Check Laravel logs for S3 errors

### Files upload but URLs don't work
- Ensure your Space is set to public
- Check CORS configuration
- Verify the DO_SPACES_URL is correct

### Performance issues
- Enable CDN on your Space
- Consider image optimization
- Use appropriate cache headers

## Cost Estimation

- Basic: $5/month for 250GB storage + 1TB bandwidth
- Each additional GB: $0.02/month
- Additional bandwidth: $0.01/GB

## Important Notes

1. **Always test in staging first** before switching production
2. **Backup your files** before migration
3. **Update your .env** file in App Platform settings, not in code
4. **Monitor your Space usage** to control costs

---

For support, refer to:
- [DigitalOcean Spaces Documentation](https://docs.digitalocean.com/products/spaces/)
- [Laravel Filesystem Documentation](https://laravel.com/docs/filesystem)