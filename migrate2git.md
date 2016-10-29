**Guide to migrate from bitbucket and svn to github.com**
======================================================

***From Bitbucket git to github.com:***
-
##### 1. Make a bare mirrored clone of the repository

```bash
git clone --mirror https://bitbucket.org/exampleuser/repository-to-mirror.git
```

##### 2. Set the remote github url

```bash
cd repository-to-mirror.git
git remote set-url --push origin https://github.com/exampleuser/mirrored
```

##### 3. Set the push location to your mirror
```bash
git push --mirror
```


***From SVN repository to github.com:***
-

##### 1. Create a new repository in github and initialize your local git repo from SVN

```bash
mkdir project
cd project
git svn init <HTTPS_SVN_URL>
```

##### 2. Mark how far back you want to start importing revisions (for all revisions):

```bash
git svn fetch
```

##### 3. Fetch everything since then

```bash
git svn rebase
```

##### 4. You can check the result of the import with Gitk.

```bash
gitk
```

##### 5. First create your empty remote repo

```bash
git remote add origin https://github.com/username/repo.git
```

##### 6. Then, optionally sync your main branch so the pull operation will automatically merge the remote master with your local master, when both contain new stuff

```bash
git config branch.master.remote origin
git config branch.master.merge refs/heads/master
```

##### 7. Configure the username and user email of the remote repo. Also by default your Git can't send big chunks. You configure the http.postBuffer as well.

```bash
git config --global user.name "mariosk"
git config --global user.email "mariosk@gmail.com"
git config --global http.postBuffer 1073741824
```

##### 8. Push all migration to the team's remote Git repository.

```bash
git push origin --mirror
```

##### 9. If you get the following: error: cannot spawn git: No such file or directory, you may try the next commands.
```bash
git push origin --all
git push origin --tags
```
