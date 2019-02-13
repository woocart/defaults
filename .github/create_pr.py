import re

if __name__ == "__main__":
    event = Event.fromPath(env["GITHUB_EVENT_PATH"])
    if not event.is_release:
        raise Exception("This event is not from release")

    with commit(event, f"update_wd_{event.release.tag_name}") as gh:
        versions = gh.get("niteoweb/woocart-docker-web", "bin/runtime/versions")
        regex = r"\/v(.+)\/"
        versions.text = re.sub(regex, f"/{event.release.tag_name}/", versions.text)
        gh.add(versions, f"Update woocart-defaults to {event.release.tag_name}")
        gh.create_pr(
            f"Update woocart-defaults to {event.release.tag_name}",
            f"This updates woocart-defaults to version {event.release.tag_name}",
        )
