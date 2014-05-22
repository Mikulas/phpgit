package pro.dite;

import org.antlr.runtime.RecognitionException;
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
        HeadWalker walker = new HeadWalker(repoDir)
        {
            @Override
            public void processFileDiff(RevCommit commit, EditList edits, String a, String b)
            {
                System.out.println(commit.getShortMessage());
                for (Edit edit : edits)
                {
                    System.out.println("\t" + edit);
                }
            }
        };

        walker.walk();
    }
}
