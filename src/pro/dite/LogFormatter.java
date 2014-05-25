package pro.dite;

import com.sun.tools.javac.util.Pair;
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

    public static final String ANSI_RESET = "\u001B[0m";
    public static final String ANSI_BLACK = "\u001B[30m";
    public static final String ANSI_RED = "\u001B[31m";
    public static final String ANSI_GREEN = "\u001B[32m";
    public static final String ANSI_YELLOW = "\u001B[33m";
    public static final String ANSI_BLUE = "\u001B[34m";
    public static final String ANSI_PURPLE = "\u001B[35m";
    public static final String ANSI_CYAN = "\u001B[36m";
    public static final String ANSI_WHITE = "\u001B[37m";

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
            System.out.println(ANSI_YELLOW + "commit " + commit.getId().getName() + ANSI_RESET);
            System.out.println("author " + commit.getAuthorIdent().getName() + " <" + commit.getAuthorIdent().getEmailAddress() + ">");
            System.out.println(commit.getFullMessage());
            if (entry == null)
            {
                continue;
            }
            for (String def : entry.getSortedRemovals())
            {
                System.out.println("    removed " + def);
            }
            for (String def : entry.getSortedAdds())
            {
                System.out.println("    added " + def);
            }
            for (Rename def : entry.getSortedRenames())
            {
                System.out.println("    renamed " + def.from + " to " + def.to);
            }
            System.out.print("\n");
        }
    }
}
