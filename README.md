# blueprint

Automated population of Nextcloud data from templates for testing.

## What

Blueprint allows populating a Nextcloud instance with data for running tests and measurements against the instance with more realistic data than just the default empty instance.

## How

```bash
occ blueprint:apply path/to/blueprint.toml
```

## As github action

```yml
- name: Apply blueprint
  uses: icewind1991/blueprint@v0.1.1
  with:
    blueprint: path/to/blueprint.toml
```
