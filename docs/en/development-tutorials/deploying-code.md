<!--
title: Deploying code
pagenumber: 7
-->

# Deploying code

This section covers how to deploy your site code onto an instance.

CWP comes with a deployment system allowing you to push code yourself onto UAT instances. You can access that system
by going to [deploy.cwp.govt.nz](http://deploy.cwp.govt.nz). You'll be asked for access details to get in, which you
should have already been given. Enter those to access the deploy site.

## deploy.cwp.govt.nz overview

Once you've logged into [deploy.cwp.govt.nz](http://deploy.cwp.govt.nz), you'll see a list of instances. Click into an
instance and you'll find the environments that code can be deployed to, as well as a table of revisions from your Gitlab
repository for that instance.

Next to the environment name you'll see whether you can deploy "Can you deploy?" as well as "Build currently deployed"
which gives you a quick summary of what code is currently on the instance and whether you have permission to deploy
a release.

Underneath you will see information about the repository currently hooked up to this instance. One instance can only
ever have one repository assigned to it, regardless of the number of environments.

Below, under "Public key", you can see a deployment key. One such key is generated for every instance and is used for
establishing trust between Gitlab (the code repository) and Deploynaut.

The table will show your recent commits, as well as any release tags (see "Tagging your code" below for how to create
a release tag.)

## Setting up the deployment key

Deploynaut needs to be able to access your Gitlab repositories. You can grant that access by adding your instance
deployment key to all your private repositories.

The public key can easily be found in the Deploynaut interface, on the project page.

![Deploynaut - project key](_images/deploynaut-project-key.jpg)

It's content is not secret (there is a private counterpart that only the server knows), so proceed to Gitlab
to add it to all your repositories that need to be included in the deployment.

If you have already been set up with a repository as part of the instance creation process, you will already have a key
associated with your master repository which should be visible in the interface. You will also be able to add this
key to other private modules simply by clicking "enable" on the relevant key on the right side.

![Gitlab - associating project key with a private module](_images/gitlab-enable-key.jpg )

<div class="notice" markdown='1'>
You will only see the deployment key if you are the owner of the repository. Check the "Team" tab to see who the owner
is. Currently, if you are not the owner, you will need to ask the person to do it for you. It's a one-off task.
</div>

If you have opted for an empty instance (or custom repository) you will need to add a "New deploy key" instead.

When including private repositories, remember they have to have a "private" flag set on them in the `composer.json`
file. See [working with modules](working-with-modules) for more information.

## Fetching changes

To get the latest changes available in the repository, hit the "Fetch latest changes" button. This is an equivalent of a
`git fetch origin` on you local machine. After a short moment you should see new tags, branches and commits coming
through and you can proceed with the deployment.

## Deploying to UAT site

Under "Environments", go to **uat**.

Under "Deploy a new release" select the revision to deploy and press "Deploy to UAT". The deployment should now start.

A new window will appear with console output of the deployment process. Depending on the availability of workers, the
process may start immediately or may be queued. As a general guideline in normal cases it should take no longer than
5 minutes to deploy.

You can save the URL and revisit it later to see how the deployment is progressing. It will not cause the deployment to
restart.

The deployment is atomic, meaning the code is uploaded into a secondary directory, before being rotated with the
original code. If the deployment fails at any point it will be rolled back without causing changes to the site. If the
deployment fails due to "unauthorised" errors, check if your project and modules have the deployment key added as
described above, and that the private modules have the "private" flag set!

For the duration of the deployment a maintenance screen will be put up using .htaccess substitution. The webserver will
return a 503 error at that time.

You can also see the deployment history for that instance in the "Deploy history" table below.

## Deploying to production site

When viewing your project on deploy.cwp.govt.nz you'll see that "Can you deploy?" is set to "No" for prod environments.
We restrict the live deployments to the platform team, since we have a responsibility to keep the site up! Before
deploying we will do a little smoke test and also take backups.

In order for you to deploy to the production site, you'll need to create a new ticket on
[helpdesk.cwp.govt.nz](http://helpdesk.cwp.govt.nz) to request the deployment. The deployment will take a copy of UAT
and copy it to production.

When creating a ticket on the helpdesk, choose **Deploy UAT to production** as the project, fill out the appropriate
fields and submit the ticket. The CWP administration team will be in touch regarding your deployment request.

## Tagging your code

Prior to deployment to a site, the best practice is to tag a certain Git revision to a version number. To do that, we'll
use Git's built-in tagging system and push the tag back so the deployment site can see it.

Start by going to your dev environment and going to where you've checked out your Git repo.

From there, let's see which tags we have:

	git tag

If you get no output, there's no tags.

Let's create a new tag. You can name the tag anything, but a typical scheme involves version numbers starting at 0.1.
It's also a good idea to create annotated tags, so the information about the originator can also be stored.

Create a new annotated tag called 1.0:

	git tag -a 1.0 -m "First release."

Tags don't get pushed automatically when you use `git push`, so we need to push the tags to Gitlab so that the
deploy.cwp.govt.nz site can see it:

	git push origin --tags

Now when you go back to [deploy.cwp.govt.nz](http://deploy.cwp.govt.nz) and press "Fetch latest changes" you'll see
a new option appearing on the deploy screen: "Deploy a tagged release". This makes it much simpler to choose the right
revision to deploy.

## Maintenance screen

Deploynaut has an automated capability to put up a maintenance screen during deployments. See [maintenance
screen](cwp-features/maintenance_screen.md) for further information.