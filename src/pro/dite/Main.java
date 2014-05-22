package pro.dite;

import org.eclipse.jgit.api.errors.GitAPIException;
import org.eclipse.jgit.diff.DiffEntry;
import org.eclipse.jgit.errors.CorruptObjectException;
import org.eclipse.jgit.errors.IncorrectObjectTypeException;
import org.eclipse.jgit.errors.MissingObjectException;
import org.eclipse.jgit.lib.ObjectId;
import org.eclipse.jgit.revwalk.RevBlob;
import org.eclipse.jgit.revwalk.RevCommit;
import org.eclipse.jgit.revwalk.RevTree;
import org.eclipse.jgit.revwalk.RevWalk;
import org.eclipse.jgit.treewalk.TreeWalk;
import org.eclipse.jgit.treewalk.WorkingTreeIterator;

import java.io.File;
import java.io.IOException;
import java.util.List;

public class Main
{

    public static void main(String[] args) throws IOException, GitAPIException
    {
        File repoDir = new File("/Users/mikulas/Projects/khanovaskola.cz-v3/.git");
        HeadWalker walker = new HeadWalker(repoDir)
        {
            @Override
            public void processCommit(RevCommit commit, List<DiffEntry> diffs) throws IOException
            {
                System.out.println(commit.getAuthorIdent().getName());
                for (DiffEntry diff : diffs)
                {
                    System.out.println("\t" + diff.getNewPath());
                    ObjectId oid = repository.resolve(diff.getNewPath());
                }
            }
        };

        walker.walk();
    }
}
