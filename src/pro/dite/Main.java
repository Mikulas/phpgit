package pro.dite;

import org.eclipse.jgit.api.errors.GitAPIException;
import org.eclipse.jgit.diff.*;
import org.eclipse.jgit.errors.CorruptObjectException;
import org.eclipse.jgit.errors.IncorrectObjectTypeException;
import org.eclipse.jgit.errors.MissingObjectException;
import org.eclipse.jgit.lib.ObjectId;
import org.eclipse.jgit.lib.ObjectLoader;
import org.eclipse.jgit.lib.ObjectStream;
import org.eclipse.jgit.revwalk.RevBlob;
import org.eclipse.jgit.revwalk.RevCommit;
import org.eclipse.jgit.revwalk.RevTree;
import org.eclipse.jgit.revwalk.RevWalk;
import org.eclipse.jgit.treewalk.TreeWalk;
import org.eclipse.jgit.treewalk.WorkingTreeIterator;
import sun.misc.IOUtils;

import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.IOException;
import java.io.StringWriter;
import java.util.ArrayList;
import java.util.List;
import java.util.Scanner;

public class Main
{

    public static void main(String[] args) throws IOException, GitAPIException
    {
        File repoDir = new File("/Users/mikulas/Projects/khanovaskola.cz-v3/.git");
        final PhpTokenizer tokenizer = new PhpTokenizer();
        HeadWalker walker = new HeadWalker(repoDir)
        {
            @Override
            public void processCommit(RevCommit commit, List<DiffEntry> diffs) throws IOException
            {
                System.out.println(commit.getAuthorIdent().getName());
                for (DiffEntry diff : diffs)
                {
                    if (diff.getChangeType() == DiffEntry.ChangeType.DELETE)
                    {
                        // TODO remove from index?
                        continue;
                    }
                    else if (!diff.getNewPath().toLowerCase().endsWith(".php"))
                    {
                        // TODO allow other extensions as well?
                        continue;
                    }

                    ArrayList<Differ.FileEdits> fileEdits = df.getEdits(diff);
                    for (Differ.FileEdits fileEdit : fileEdits)
                    {
                        // TODO for both old and new file, detect class and method boundaries
                        // if either hits, add to index
                    }

                    System.out.println("\t" + diff.getNewPath());
                    System.out.println("\t" + diff.getChangeType());

                    String contentOld = getBlobContent(diff.getOldId().toObjectId());
                    String contentNew = getBlobContent(diff.getNewId().toObjectId());

                    RawText a = new RawText(contentOld.getBytes());
                    RawText b = new RawText(contentOld.getBytes());
                    EditList r = MyersDiff.INSTANCE.diff(RawTextComparator.DEFAULT, a, b);

                    df.format(diff);
                    System.out.print(diffOut.toString("UTF-8"));
                    diffOut.reset();
                }
            }
        };

        walker.walk();
    }
}
