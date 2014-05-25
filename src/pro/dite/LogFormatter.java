package pro.dite;

import org.eclipse.jgit.api.Git;
import org.eclipse.jgit.api.errors.GitAPIException;
import org.eclipse.jgit.errors.AmbiguousObjectException;
import org.eclipse.jgit.lib.ObjectId;
import org.eclipse.jgit.lib.Repository;
import org.eclipse.jgit.revwalk.RevCommit;

import java.io.IOException;
import java.util.ArrayList;

public class LogFormatter
{

    private Repository repo;
    private Git git;

    public LogFormatter(Repository repo)
    {
        this.repo = repo;
        git = new Git(repo);
    }

    public void print(Cache cache) throws IOException, GitAPIException
    {
        ObjectId head = repo.resolve("HEAD");
        RevCommit parent = null;
        for (RevCommit commit : git.log().add(head).call())
        {
            CacheEntry entry = cache.entries.get(commit.getId().toString());
            System.out.println("commit " + commit.getId().getName());
            System.out.println("author " + commit.getAuthorIdent().getName() + " <" + commit.getAuthorIdent().getEmailAddress() + ">");
            System.out.println(commit.getFullMessage());
            if (entry == null)
            {
                continue;
            }
            for (String def : entry.index)
            {
                System.out.println("\t" + def);
            }
            System.out.print("\n");
        }
    }
}