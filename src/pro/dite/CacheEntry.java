package pro.dite;

import org.eclipse.jgit.revwalk.RevCommit;

import java.io.Serializable;
import java.util.HashSet;

public class CacheEntry implements Serializable
{
    final HashSet<String> index = new HashSet<String>();
    String author;

    public CacheEntry(RevCommit commit)
    {
        this.author = commit.getAuthorIdent().getEmailAddress();
    }
}
